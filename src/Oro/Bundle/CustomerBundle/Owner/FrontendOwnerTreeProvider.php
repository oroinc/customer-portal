<?php

namespace Oro\Bundle\CustomerBundle\Owner;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CustomerBundle\Async\Topic\CustomerCalculateOwnerTreeCacheTopic;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\EntityBundle\Tools\DatabaseChecker;
use Oro\Bundle\SecurityBundle\Owner\AbstractOwnerTreeProvider;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadataProviderInterface;
use Oro\Bundle\SecurityBundle\Owner\OwnerTreeBuilderInterface;
use Oro\Bundle\SecurityBundle\Owner\OwnerTreeInterface;
use Oro\Component\DoctrineUtils\ORM\QueryUtil;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * This class is used to build the tree of owners for customers.
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class FrontendOwnerTreeProvider extends AbstractOwnerTreeProvider implements CustomerAwareOwnerTreeInterface
{
    /**
     * clear cache for inactive customers/customer users every hour
     */
    private const DEFAULT_CACHE_TTL = 86400;

    private ManagerRegistry                    $doctrine;
    private TokenStorageInterface              $tokenStorage;
    private OwnershipMetadataProviderInterface $ownershipMetadataProvider;
    private ?Customer                          $currentCustomer = null;
    private MessageProducerInterface           $messageProducer;
    private int                                $cacheTtl;

    public function __construct(
        ManagerRegistry $doctrine,
        DatabaseChecker $databaseChecker,
        CacheInterface $cache,
        OwnershipMetadataProviderInterface $ownershipMetadataProvider,
        TokenStorageInterface $tokenStorage,
        MessageProducerInterface $messageProducer
    ) {
        parent::__construct($databaseChecker, $cache);
        $this->doctrine = $doctrine;
        $this->ownershipMetadataProvider = $ownershipMetadataProvider;
        $this->tokenStorage = $tokenStorage;
        $this->messageProducer = $messageProducer;
    }

    /**
     * {@inheritDoc}
     */
    public function supports(): bool
    {
        return null !== $this->getCustomerUser();
    }

    /**
     * Warms up cache for recent customer users visitors.
     * {@inheritDoc}
     */
    public function warmUpCache(): void
    {
        $this->messageProducer->send(
            CustomerCalculateOwnerTreeCacheTopic::getName(),
            [CustomerCalculateOwnerTreeCacheTopic::CACHE_TTL => $this->getCacheTtl()]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getTree(): OwnerTreeInterface
    {
        $cacheKey = $this->getOwnerTreeCacheKey();
        if (!$cacheKey) {
            return $this->loadTree();
        }

        return $this->cache->get($cacheKey, function (ItemInterface $item) {
            $item->expiresAfter($this->getCacheTtl());
            return $this->loadTree();
        });
    }

    /**
     * {@inheritDoc}
     */
    public function getTreeByBusinessUnit($businessUnit): OwnerTreeInterface
    {
        $this->currentCustomer = $businessUnit;
        $tree = $this->getTree();
        $this->currentCustomer = null;

        return $tree;
    }

    public function setCacheTtl(int $cacheTtl): void
    {
        $this->cacheTtl = $cacheTtl;
    }

    /**
     * {@inheritDoc}
     */
    protected function fillTree(OwnerTreeBuilderInterface $tree): void
    {
        $customerIds = $this->addAncestorCustomers($tree);

        [$customerUsers, $columnMap] = $this->executeCustomerUsersQuery($customerIds);

        foreach ($customerUsers as $customerUser) {
            $userId = $this->getId($customerUser, $columnMap['userId']);
            $orgId = $this->getId($customerUser, $columnMap['orgId']);
            $customerId = $this->getId($customerUser, $columnMap['customerId']);

            $tree->addUser($userId, $customerId);
            $tree->addUserOrganization($userId, $orgId);

            if (null !== $customerId) {
                $tree->addUserBusinessUnit($userId, $orgId, $customerId);
            }
        }
    }

    protected function getId(array $item, string $property): ?int
    {
        $id = $item[$property];
        if (null !== $id) {
            $id = (int)$id;
        }

        return $id;
    }

    /**
     * @return array [rows, columnMap]
     */
    private function executeQuery(Connection $connection, Query $query): array
    {
        $parsedQuery = QueryUtil::parseQuery($query);
        $executableQuery = QueryUtil::getExecutableSql($query, $parsedQuery);

        return [
            $connection->executeQuery($executableQuery),
            array_flip($parsedQuery->getResultSetMapping()->scalarMappings),
        ];
    }

    protected function setSubordinateBusinessUnitIds(OwnerTreeBuilderInterface $tree, $businessUnits): void
    {
        foreach ($businessUnits as $parentId => $businessUnitIds) {
            $tree->setSubordinateBusinessUnitIds($parentId, $businessUnitIds);
        }
    }

    private function getManagerForClass(string $className): EntityManagerInterface
    {
        return $this->doctrine->getManagerForClass($className);
    }

    private function getRepository(string $entityClass): EntityRepository
    {
        return $this->getManagerForClass($entityClass)->getRepository($entityClass);
    }

    private function executeCustomerUsersQuery(array $customerIds = []): array
    {
        $customerUserClass = $this->ownershipMetadataProvider->getUserClass();
        $connection = $this->getManagerForClass($customerUserClass)->getConnection();

        $queryBuilder = $this
            ->getRepository($customerUserClass)
            ->createQueryBuilder('au');

        $queryBuilder->select(
            'au.id as userId, IDENTITY(au.organization) as orgId, IDENTITY(au.customer) as customerId'
        );

        if ($customerIds) {
            sort($customerIds);
            $query = $queryBuilder
                ->where($queryBuilder->expr()->in('au.customer', ':customerIds'))
                ->getQuery()
                ->setParameter('customerIds', $customerIds);
        } else {
            $query = $queryBuilder->getQuery();
        }

        return $this->executeQuery($connection, $query);
    }

    /**
     * Gets all ancestor customers including current top customer.
     *
     * @return array|int[] ancestor customer ids
     */
    private function addAncestorCustomers(OwnerTreeBuilderInterface $tree): array
    {
        $topCustomerId = $this->getTopLevelCustomerId();

        if (!$topCustomerId) {
            [$customers, $columnMap] = $this->getAncestorIterator();
            $this->addAllCustomers($tree, $customers, $columnMap);

            return [];
        }

        return $this->addCustomersSubtree($tree, $topCustomerId);
    }

    private function getTopLevelCustomerId(): ?int
    {
        if ($this->currentCustomer) {
            return $this->getRootCustomer($this->currentCustomer);
        }

        $customerUser = $this->getCustomerUser();
        if (!$customerUser) {
            return null;
        }

        $cacheKey = $this->getCustomerUserCacheKey($customerUser);
        return $this->cache->get($cacheKey, function () use ($customerUser) {
            $customer = $customerUser->getCustomer();
            return $customer ? $this->getRootCustomer($customer) : null;
        });
    }

    private function getRootCustomer(Customer $customer): int
    {
        $originalId = $customerId = $customer->getId();

        while ($parentId = $this->getCustomerParentId($customerId)) {
            $customerId = $parentId;

            // Prevent infinite loop in case of a cycle
            if ($customerId === $originalId) {
                break;
            }
        }

        return $customerId;
    }

    private function getCustomerParentId(int $customerId): ?int
    {
        $customerClass = $this->ownershipMetadataProvider->getBusinessUnitClass();

        $queryBuilder = $this
            ->getRepository($customerClass)
            ->createQueryBuilder('c');

        return $queryBuilder
            ->select('IDENTITY(c.parent)')
            ->where($queryBuilder->expr()->eq('c.id', ':id'))
            ->getQuery()
            ->setParameter('id', $customerId)
            ->getOneOrNullResult(AbstractQuery::HYDRATE_SINGLE_SCALAR);
    }

    private function getCustomerUserCacheKey(CustomerUser $customerUser): string
    {
        return sprintf('user_%d', $customerUser->getId());
    }

    private function getOwnerTreeCacheKey(): ?string
    {
        $customerId = $this->getTopLevelCustomerId();
        if (!$customerId) {
            return null;
        }

        return sprintf('%s_%s', self::CACHE_KEY, $customerId);
    }

    private function getCustomerUser(): ?CustomerUser
    {
        $token = $this->tokenStorage->getToken();
        if (null === $token) {
            return null;
        }

        $user = $token->getUser();
        if (!$user instanceof CustomerUser) {
            return null;
        }

        return $user;
    }

    private function addAllCustomers(OwnerTreeBuilderInterface $tree, iterable $customers, array $columnMap): void
    {
        $businessUnitRelations = [];
        foreach ($customers as $customer) {
            $orgId = $this->getId($customer, $columnMap['orgId']);
            if (!$orgId) {
                continue;
            }

            $buId = $this->getId($customer, $columnMap['id']);

            $tree->addBusinessUnit($buId, $orgId);
            $businessUnitRelations[$buId] = $this->getId($customer, $columnMap['parentId']);
        }

        $customerClass = $this->ownershipMetadataProvider->getBusinessUnitClass();
        $this->setSubordinateBusinessUnitIds($tree, $this->buildTree($businessUnitRelations, $customerClass));
    }

    /**
     * Adds customers subtree where topCustomerId is an id of the root customer node.
     */
    private function addCustomersSubtree(
        OwnerTreeBuilderInterface $tree,
        int $parentCustomerId
    ): array {
        $customerClass = $this->ownershipMetadataProvider->getBusinessUnitClass();
        $customerData = $this
            ->getRepository($customerClass)
            ->createQueryBuilder('a')
            ->select('a.id, IDENTITY(a.organization) orgId, IDENTITY(a.parent) parentId')
            ->where('a.id = :id')
            ->setParameter('id', $parentCustomerId)
            ->getQuery()
            ->getScalarResult();
        $businessUnitRelations[$parentCustomerId] = $customerData[0]['parentId'];
        $customerIds[] = $parentCustomerId;
        $tree->addBusinessUnit($parentCustomerId, $customerData[0]['orgId']);

        $this->collectSubtree($tree, [$parentCustomerId], $businessUnitRelations, $customerIds);

        $this->setSubordinateBusinessUnitIds(
            $tree,
            $this->buildTree($businessUnitRelations, $customerClass)
        );

        return $customerIds;
    }

    private function collectSubtree(
        OwnerTreeBuilderInterface $tree,
        array $parentCustomerIds,
        array &$businessUnitRelations,
        array &$customerIds
    ): void {
        $parents = [];

        [$customers, $columnMap] = $this->getAncestorIterator($parentCustomerIds);
        foreach ($customers as $customer) {
            $orgId = $this->getId($customer, $columnMap['orgId']);
            if (!$orgId) {
                continue;
            }

            $buId = $this->getId($customer, $columnMap['id']);
            $parentBuId = $this->getId($customer, $columnMap['parentId']);

            $tree->addBusinessUnit($buId, $orgId);
            $businessUnitRelations[$buId] = $parentBuId;
            $customerIds[] = $buId;
            $parents[] = $buId;
        }

        $ids = [];
        foreach ($parents as $customer) {
            $ids[] = $customer;
            if (count($ids) === 1000) {
                $this->collectSubtree($tree, $ids, $businessUnitRelations, $customerIds);
                $ids = [];
            }
        }
        if ($ids) {
            $this->collectSubtree($tree, $ids, $businessUnitRelations, $customerIds);
        }
    }

    private function getAncestorIterator(array $parentIds = []): array
    {
        $customerClass = $this->ownershipMetadataProvider->getBusinessUnitClass();
        $connection = $this->getManagerForClass($customerClass)->getConnection();

        $qb = $this->getRepository($customerClass)
            ->createQueryBuilder('a')
            ->select(
                'a.id, IDENTITY(a.organization) orgId, IDENTITY(a.parent) parentId'
                . ', (CASE WHEN a.parent IS NULL THEN 0 ELSE 1 END) AS HIDDEN ORD'
            )
            ->addOrderBy('ORD, parentId, a.id', 'ASC');
        if (count($parentIds)) {
            $qb->where('a.parent in (:parent)')
                ->setParameter('parent', $parentIds);
        }

        [$customers, $columnMap] = $this->executeQuery(
            $connection,
            $qb->getQuery()
        );

        return [$customers, $columnMap];
    }

    private function getCacheTtl(): int
    {
        return $this->cacheTtl ?? self::DEFAULT_CACHE_TTL;
    }
}
