<?php

namespace Oro\Bundle\CustomerBundle\Entity\Repository;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;

/**
 * Reset customer user for entity owned by given customer user.
 */
trait ResetCustomerUserTrait
{
    /**
     * @param CustomerUser $customerUser
     * @param array $updatedEntities
     * @return int
     */
    public function resetCustomerUser(CustomerUser $customerUser, array $updatedEntities = [])
    {
        /** @var QueryBuilder $qb */
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb->update($this->getEntityName(), 'e')
            ->set('e.customerUser', ':newCustomerUser')
            ->where($qb->expr()->eq('e.customerUser', ':oldCustomerUser'))
            ->setParameter('newCustomerUser', null)
            ->setParameter('oldCustomerUser', $customerUser);

        if ($updatedEntities) {
            $qb->andWhere($qb->expr()->in('e', ':updatedEntities'))
                ->setParameter('updatedEntities', $updatedEntities);
        }

        return $qb->getQuery()->execute();
    }

    public function getRelatedEntitiesCount(CustomerUser $customerUser): int
    {
        /** @var QueryBuilder $qb */
        $qb = $this->createQueryBuilder('e');
        $qb->select($qb->expr()->count('e.id'))
            ->where($qb->expr()->eq('e.customerUser', ':customerUser'))
            ->setParameter('customerUser', $customerUser);

        return $qb->getQuery()->getSingleScalarResult();
    }
}
