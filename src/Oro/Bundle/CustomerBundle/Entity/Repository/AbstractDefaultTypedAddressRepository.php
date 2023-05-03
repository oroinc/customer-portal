<?php

namespace Oro\Bundle\CustomerBundle\Entity\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Component\DoctrineUtils\ORM\QueryBuilderUtil;
use Oro\Component\DoctrineUtils\ORM\ResultSetMappingUtil;
use Oro\Component\DoctrineUtils\ORM\SqlQueryBuilder;

/**
 * Abstract repository for DefaultTypedAddress ORM entities.
 */
abstract class AbstractDefaultTypedAddressRepository extends EntityRepository
{
    /**
     * @param AclHelper $aclHelper
     * @return array
     */
    public function getAddresses(AclHelper $aclHelper)
    {
        $query = $aclHelper->apply($this->createQueryBuilder('a'));

        return $query->getResult();
    }

    /**
     * @param object $frontendOwner
     * @param string $type
     * @return QueryBuilder
     */
    public function getAddressesByTypeQueryBuilder($frontendOwner, $type)
    {
        $qb = $this->createQueryBuilder('a');
        $qb
            ->innerJoin(
                'a.types',
                'types',
                Join::WITH,
                $qb->expr()->eq('IDENTITY(types.type)', ':type')
            )
            ->setParameter('type', $type)
            ->andWhere($qb->expr()->eq('a.frontendOwner', ':frontendOwner'))
            ->setParameter('frontendOwner', $frontendOwner);

        return $qb;
    }

    /**
     * @param object $frontendOwner
     * @param string|null $type
     * @return QueryBuilder
     */
    public function getDefaultAddressesQueryBuilder($frontendOwner, $type = null)
    {
        $qb = $this->createQueryBuilder('a');
        $joinConditions = $qb->expr()->andX($qb->expr()->eq('types.default', ':isDefault'));
        if ($type) {
            $joinConditions->add($qb->expr()->eq('IDENTITY(types.type)', ':type'));
            $qb->setParameter('type', $type);
        }

        $qb
            ->innerJoin('a.types', 'types', Join::WITH, $joinConditions)
            ->setParameter('isDefault', true)
            ->andWhere($qb->expr()->eq('a.frontendOwner', ':frontendOwner'))
            ->setParameter('frontendOwner', $frontendOwner);

        return $qb;
    }

    public function updateNotListedPrimaryAddresses(array $ownerToId): void
    {
        $qb = $this->createQueryBuilder('a');
        $qb->update()
            ->set('a.primary', ':newPrimary')
            ->where($qb->expr()->in('a.frontendOwner', ':frontendOwners'))
            ->andWhere($qb->expr()->eq('a.primary', ':isPrimary'))
            ->andWhere($qb->expr()->notIn('a.id', ':ids'))
            ->setParameter('newPrimary', false)
            ->setParameter('isPrimary', true)
            ->setParameter('frontendOwners', array_keys($ownerToId))
            ->setParameter('ids', array_values($ownerToId));

        $qb->getQuery()->execute();
    }

    public function updateNotListedDefaultAddresses(string $type, array $ownerToId): void
    {
        $metadata = $this->getEntityManager()->getClassMetadata($this->getClassName());
        $typesMetadata = $this->getEntityManager()->getClassMetadata(
            $metadata->getAssociationMapping('types')['targetEntity']
        );

        $expr = $this->getEntityManager()->getExpressionBuilder();
        $rsm = ResultSetMappingUtil::createResultSetMapping(
            $this->getEntityManager()->getConnection()->getDatabasePlatform()
        );
        $updateQB = new SqlQueryBuilder($this->getEntityManager(), $rsm);
        $updateQB->update($typesMetadata->getTableName(), 't')
            ->innerJoin(
                't',
                $metadata->getTableName(),
                'a',
                $expr->eq(
                    QueryBuilderUtil::getField('t', $typesMetadata->getSingleAssociationJoinColumnName('address')),
                    'a.id'
                )
            )
            ->where(
                $expr->andX(
                    $expr->eq('t.is_default', ':isDefault'),
                    $expr->in('a.frontend_owner_id', ':frontendOwnerIds'),
                    $expr->notIn('a.id', ':ids'),
                    $expr->eq('t.type_name', ':typeName')
                )
            );
        $updateQB->setParameters(
            [
                'isDefault' => true,
                'frontendOwnerIds' => array_keys($ownerToId),
                'ids' => array_values($ownerToId),
                'typeName' => $type
            ],
            [
                'isDefault' => Types::BOOLEAN,
                'frontendOwnerIds' => Connection::PARAM_INT_ARRAY,
                'ids' => Connection::PARAM_INT_ARRAY,
                'typeName' => Types::STRING
            ],
        );

        $updateQB
            ->set('is_default', ':newIsDefault')
            ->setParameter('newIsDefault', false, Types::BOOLEAN);

        $updateQB->execute();
    }
}
