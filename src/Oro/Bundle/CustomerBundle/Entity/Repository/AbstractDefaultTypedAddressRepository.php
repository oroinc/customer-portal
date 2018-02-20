<?php

namespace Oro\Bundle\CustomerBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

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
}
