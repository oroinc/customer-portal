<?php

namespace Oro\Bundle\CustomerBundle\Owner;

use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\EntityBundle\Tools\DatabaseChecker;
use Oro\Bundle\SecurityBundle\Owner\AbstractOwnerTreeProvider;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadataProviderInterface;
use Oro\Bundle\SecurityBundle\Owner\OwnerTreeBuilderInterface;
use Oro\Component\DoctrineUtils\ORM\QueryUtil;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

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
    public function supports()
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
    protected function fillTree(OwnerTreeBuilderInterface $tree)
    {
        $ownershipMetadataProvider = $this->getOwnershipMetadataProvider();
        $customerUserClass = $ownershipMetadataProvider->getUserClass();
        $customerClass = $ownershipMetadataProvider->getBusinessUnitClass();
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
        foreach ($customers as $customer) {
            $orgId = $this->getId($customer, $columnMap['orgId']);
            if (null !== $orgId) {
                $buId = $this->getId($customer, $columnMap['id']);
                $tree->addBusinessUnit($buId, $orgId);
                $tree->addBusinessUnitRelation($buId, $this->getId($customer, $columnMap['parentId']));
            }
        }

        $tree->buildTree();

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
    protected function getId($item, $property)
    {
        $id = $item[$property];

        return null !== $id ? (int)$id : null;
    }

    /**
     * @param Connection $connection
     * @param Query      $query
     *
     * @return array [rows, columnMap]
     */
    protected function executeQuery(Connection $connection, Query $query)
    {
        $parsedQuery = QueryUtil::parseQuery($query);
        $executableQuery = QueryUtil::getExecutableSql($query, $parsedQuery);

        return [
            $connection->executeQuery($executableQuery),
            array_flip($parsedQuery->getResultSetMapping()->scalarMappings)
        ];
    }

    /**
     * @param string $className
     *
     * @return EntityManager
     */
    protected function getManagerForClass($className)
    {
        return $this->doctrine->getManagerForClass($className);
    }

    /**
     * @param string $entityClass
     *
     * @return EntityRepository
     */
    protected function getRepository($entityClass)
    {
        return $this->getManagerForClass($entityClass)->getRepository($entityClass);
    }

    /**
     * @return OwnershipMetadataProviderInterface
     */
    protected function getOwnershipMetadataProvider()
    {
        return $this->ownershipMetadataProvider;
    }
}
