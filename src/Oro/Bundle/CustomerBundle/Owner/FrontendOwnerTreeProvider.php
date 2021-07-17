<?php

namespace Oro\Bundle\CustomerBundle\Owner;

use Doctrine\Common\Cache\CacheProvider;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CustomerBundle\Async\Topics;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Model\OwnerTreeMessageFactory;
use Oro\Bundle\EntityBundle\Tools\DatabaseChecker;
use Oro\Bundle\SecurityBundle\Owner\AbstractOwnerTreeProvider;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadataProviderInterface;
use Oro\Bundle\SecurityBundle\Owner\OwnerTreeBuilderInterface;
use Oro\Bundle\SecurityBundle\Owner\OwnerTreeInterface;
use Oro\Component\DoctrineUtils\ORM\QueryUtil;
use Oro\Component\MessageQueue\Client\Message;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * This class is used to build the tree of owners for customers.
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class FrontendOwnerTreeProvider extends AbstractOwnerTreeProvider implements CustomerAwareOwnerTreeInterface
{
    /**
     * @var int
     * clear cache for inactive customers/customer users every hour
     */
    private const DEFAULT_CACHE_TTL = 86400;

    /** @var ManagerRegistry */
    private $doctrine;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var OwnershipMetadataProviderInterface */
    private $ownershipMetadataProvider;

    /** @var Customer */
    private $currentCustomer;

    /** @var MessageProducerInterface */
    private $messageProducer;

    /** @var OwnerTreeMessageFactory */
    private $ownerTreeMessageFactory;

    public function __construct(
        ManagerRegistry $doctrine,
        DatabaseChecker $databaseChecker,
        CacheProvider $cache,
        OwnershipMetadataProviderInterface $ownershipMetadataProvider,
        TokenStorageInterface $tokenStorage,
        MessageProducerInterface $messageProducer,
        OwnerTreeMessageFactory $ownerTreeMessageFactory
    ) {
        parent::__construct($databaseChecker, $cache);
        $this->doctrine = $doctrine;
        $this->ownershipMetadataProvider = $ownershipMetadataProvider;
        $this->tokenStorage = $tokenStorage;
        $this->messageProducer = $messageProducer;
        $this->ownerTreeMessageFactory = $ownerTreeMessageFactory;
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
     * {@inheritdoc}
     */
    public function warmUpCache(): void
    {
        $messageData = $this->ownerTreeMessageFactory->createMessage($this->getCacheTtl());
        $this->messageProducer->send(Topics::CALCULATE_OWNER_TREE_CACHE, new Message($messageData));
    }

    /**
     * {@inheritdoc}
     */
    public function getTree(): OwnerTreeInterface
    {
        $cacheKey = $this->getOwnerTreeCacheKey();
        if (!$cacheKey) {
            return $this->loadTree();
        }

        $tree = $this->cache->fetch($cacheKey);
        if (!$tree) {
            $tree = $this->loadTree();
            $this->cache->save($cacheKey, $tree, $this->getCacheTtl());
        }

        return $tree;
    }

    /**
     * @param object $businessUnit
     * @return OwnerTreeInterface
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
     * {@inheritdoc}
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
     * @param Connection $connection
     * @param Query      $query
     *
     * @return array [rows, columnMap]
     */
    private function executeQuery(Connection $connection, Query $query): array
    {
        $parsedQuery = QueryUtil::parseQuery($query);
        $executableQuery = QueryUtil::getExecutableSql($query, $parsedQuery);

        return [
            $connection->executeQuery($executableQuery),
            array_flip($parsedQuery->getResultSetMapping()->scalarMappings)
        ];
    }

    protected function setSubordinateBusinessUnitIds(OwnerTreeBuilderInterface $tree, $businessUnits)
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
     * @param OwnerTreeBuilderInterface $tree
     * @return array|int[] ancestor customer ids
     */
    private function addAncestorCustomers(OwnerTreeBuilderInterface $tree): array
    {
        $customerClass = $this->ownershipMetadataProvider->getBusinessUnitClass();
        $connection = $this->getManagerForClass($customerClass)->getConnection();

        [$customers, $columnMap] = $this->executeQuery(
            $connection,
            $this
                ->getRepository($customerClass)
                ->createQueryBuilder('a')
                ->select(
                    'a.id, IDENTITY(a.organization) orgId, IDENTITY(a.parent) parentId'
                    . ', (CASE WHEN a.parent IS NULL THEN 0 ELSE 1 END) AS HIDDEN ORD'
                )
                ->addOrderBy('ORD, parentId, a.id', 'ASC')
                ->getQuery()
        );

        $topCustomerId = $this->getTopLevelCustomerId();
        if (!$topCustomerId) {
            $this->addAllCustomers($tree, $customers, $columnMap);

            return [];
        }

        return $this->addCustomersSubtree($tree, $topCustomerId, $customers, $columnMap);
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
        $topCustomerId = $this->cache->fetch($cacheKey);

        if (false === $topCustomerId) {
            $topCustomerId = null;
            $customer = $customerUser->getCustomer();

            if ($customer) {
                $topCustomerId = $this->getRootCustomer($customer);
            }

            $this->cache->save($cacheKey, $topCustomerId);
        }

        return $topCustomerId;
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
            ->getSingleScalarResult();
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
     *
     * @param OwnerTreeBuilderInterface $tree
     * @param int|null $topCustomerId
     * @param array $customers
     * @param array $columnMap
     * @return array
     */
    private function addCustomersSubtree(
        OwnerTreeBuilderInterface $tree,
        ?int $topCustomerId,
        Iterable $customers,
        array $columnMap
    ): array {
        $customersToProcess = [$topCustomerId => true];
        $businessUnitRelations = [];
        $customerIds = [];
        $hasChanges = true;

        while ($hasChanges) {
            $hasChanges = false;

            foreach ($customers as $customer) {
                $orgId = $this->getId($customer, $columnMap['orgId']);
                if (!$orgId) {
                    continue;
                }

                $buId = $this->getId($customer, $columnMap['id']);
                $parentBuId = $this->getId($customer, $columnMap['parentId']);
                if (isset($customersToProcess[$parentBuId]) && !isset($customersToProcess[$buId])) {
                    $customersToProcess[$buId] = true;
                    $hasChanges = true;
                }

                if (isset($customersToProcess[$buId]) && $customersToProcess[$buId]) {
                    $tree->addBusinessUnit($buId, $orgId);
                    $businessUnitRelations[$buId] = $parentBuId;
                    $customersToProcess[$buId] = false;
                    $customerIds[] = $buId;
                }
            }
            $customers->execute();
        }

        $customerClass = $this->ownershipMetadataProvider->getBusinessUnitClass();
        $this->setSubordinateBusinessUnitIds($tree, $this->buildTree($businessUnitRelations, $customerClass));

        return $customerIds;
    }

    private function getCacheTtl(): int
    {
        return $this->cacheTtl ?? self::DEFAULT_CACHE_TTL;
    }
}
