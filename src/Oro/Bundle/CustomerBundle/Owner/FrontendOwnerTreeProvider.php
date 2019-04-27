<?php

namespace Oro\Bundle\CustomerBundle\Owner;

use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\EntityBundle\Tools\DatabaseChecker;
use Oro\Bundle\SecurityBundle\Owner\AbstractOwnerTreeProvider;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadataProviderInterface;
use Oro\Bundle\SecurityBundle\Owner\OwnerTreeBuilderInterface;
use Oro\Component\DoctrineUtils\ORM\QueryUtil;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * The provider for storefront owner tree.
 */
class FrontendOwnerTreeProvider extends AbstractOwnerTreeProvider
{
    /** @var ManagerRegistry */
    private $doctrine;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var OwnershipMetadataProviderInterface */
    private $ownershipMetadataProvider;

    /**
     * @param ManagerRegistry                    $doctrine
     * @param DatabaseChecker                    $databaseChecker
     * @param CacheProvider                      $cache
     * @param OwnershipMetadataProviderInterface $ownershipMetadataProvider
     * @param TokenStorageInterface              $tokenStorage
     */
    public function __construct(
        ManagerRegistry $doctrine,
        DatabaseChecker $databaseChecker,
        CacheProvider $cache,
        OwnershipMetadataProviderInterface $ownershipMetadataProvider,
        TokenStorageInterface $tokenStorage
    ) {
        parent::__construct($databaseChecker, $cache);
        $this->doctrine = $doctrine;
        $this->ownershipMetadataProvider = $ownershipMetadataProvider;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritDoc}
     */
    public function supports(): bool
    {
        $token = $this->tokenStorage->getToken();
        if (null === $token) {
            return false;
        }

        return $token->getUser() instanceof CustomerUser;
    }

    /**
     * {@inheritdoc}
     */
    protected function fillTree(OwnerTreeBuilderInterface $tree): void
    {
        $customerUserClass = $this->ownershipMetadataProvider->getUserClass();
        $customerClass = $this->ownershipMetadataProvider->getBusinessUnitClass();
        $connection = $this->getManagerForClass($customerUserClass)->getConnection();

        list($customers, $columnMap) = $this->executeQuery(
            $connection,
            $this
                ->getRepository($customerClass)
                ->createQueryBuilder('a')
                ->select(
                    'a.id, IDENTITY(a.organization) orgId, IDENTITY(a.parent) parentId'
                    . ', (CASE WHEN a.parent IS NULL THEN 0 ELSE 1 END) AS HIDDEN ORD'
                )
                ->addOrderBy('ORD, parentId', 'ASC')
                ->getQuery()
        );

        $businessUnitRelations = [];

        foreach ($customers as $customer) {
            $orgId = $this->getId($customer, $columnMap['orgId']);
            if (null !== $orgId) {
                $buId = $this->getId($customer, $columnMap['id']);
                $tree->addBusinessUnit($buId, $orgId);
                $businessUnitRelations[$buId] = $this->getId($customer, $columnMap['parentId']);
            }
        }

        $this->setSubordinateBusinessUnitIds($tree, $this->buildTree($businessUnitRelations, $customerClass));

        list($customerUsers, $columnMap) = $this->executeQuery(
            $connection,
            $this
                ->getRepository($customerUserClass)
                ->createQueryBuilder('au')
                ->select(
                    'au.id as userId, IDENTITY(au.organization) as orgId, IDENTITY(au.customer) as customerId'
                )
                ->addOrderBy('orgId')
                ->getQuery()
        );
        $lastUserId = false;
        $lastOrgId = false;
        $processedUsers = [];
        foreach ($customerUsers as $customerUser) {
            $userId = $this->getId($customerUser, $columnMap['userId']);
            $orgId = $this->getId($customerUser, $columnMap['orgId']);
            $customerId = $this->getId($customerUser, $columnMap['customerId']);
            if ($userId !== $lastUserId && !isset($processedUsers[$userId])) {
                $tree->addUser($userId, $customerId);
                $processedUsers[$userId] = true;
            }
            if ($orgId !== $lastOrgId || $userId !== $lastUserId) {
                $tree->addUserOrganization($userId, $orgId);
            }
            if (null !== $customerId) {
                $tree->addUserBusinessUnit($userId, $orgId, $customerId);
            }
            $lastUserId = $userId;
            $lastOrgId = $orgId;
        }
    }

    /**
     * @param array  $item
     * @param string $property
     *
     * @return int|null
     */
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

    /**
     * @param OwnerTreeBuilderInterface $tree
     * @param $businessUnits
     */
    protected function setSubordinateBusinessUnitIds(OwnerTreeBuilderInterface $tree, $businessUnits)
    {
        foreach ($businessUnits as $parentId => $businessUnitIds) {
            $tree->setSubordinateBusinessUnitIds($parentId, $businessUnitIds);
        }
    }

    /**
     * @param string $className
     *
     * @return EntityManagerInterface
     */
    private function getManagerForClass(string $className): EntityManagerInterface
    {
        return $this->doctrine->getManagerForClass($className);
    }

    /**
     * @param string $entityClass
     *
     * @return EntityRepository
     */
    private function getRepository(string $entityClass): EntityRepository
    {
        return $this->getManagerForClass($entityClass)->getRepository($entityClass);
    }
}
