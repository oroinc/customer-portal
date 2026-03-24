<?php

namespace Oro\Bundle\CustomerBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\WebsiteBundle\Entity\Website;

/**
 * Standard Symfony`s repository for CustomerUser entity
 */
class CustomerUserRoleRepository extends EntityRepository
{
    /**
     * Checks is role default for website
     *
     * @param CustomerUserRole $role
     * @return bool
     */
    public function isDefaultOrGuestForWebsite(CustomerUserRole $role)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        return (bool)$qb->select('1')
            ->from(Website::class, 'website')
            ->where($qb->expr()->eq('website.default_role', ':role'))
            ->orWhere($qb->expr()->eq('website.guest_role', ':role'))
            ->setParameter('role', $role)
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();
    }

    /**
     * Checks if there are at least one user assigned to the given role
     *
     * @param CustomerUserRole $role
     * @return bool
     */
    public function hasAssignedUsers(CustomerUserRole $role)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $findResult = $qb
            ->select('customerUser.id')
            ->from(CustomerUser::class, 'customerUser')
            ->innerJoin('customerUser.userRoles', 'CustomerUserRole')
            ->where($qb->expr()->eq('CustomerUserRole', ':CustomerUserRole'))
            ->setParameter('CustomerUserRole', $role)
            ->setMaxResults(1)
            ->getQuery()
            ->getArrayResult();

        return !empty($findResult);
    }

    /**
     * Return array of assigned users to the given role
     *
     * @param CustomerUserRole $role
     * @return CustomerUser[]
     */
    public function getAssignedUsers(CustomerUserRole $role)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $findResult = $qb
            ->select('customerUser')
            ->from(CustomerUser::class, 'customerUser')
            ->innerJoin('customerUser.userRoles', 'CustomerUserRole')
            ->where($qb->expr()->eq('CustomerUserRole', ':CustomerUserRole'))
            ->setParameter('CustomerUserRole', $role)
            ->getQuery()
            ->getResult();

        return $findResult;
    }

    public function getAvailableRolesByCustomerUserQueryBuilder(?int $customerId): QueryBuilder
    {
        $qb = $this->createQueryBuilder('CustomerUserRole');

        $expr = $qb->expr()->isNull('CustomerUserRole.customer');
        if ($customerId) {
            $expr = $qb->expr()->orX(
                $expr,
                $qb->expr()->eq('CustomerUserRole.customer', ':customer')
            );
            $qb->setParameter('customer', $customerId);
        }

        $qb->where($expr);

        return $qb;
    }
}
