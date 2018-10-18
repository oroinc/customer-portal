<?php

namespace Oro\Bundle\CustomerBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\EntityBundle\ORM\Repository\BatchIteratorInterface;
use Oro\Bundle\EntityBundle\ORM\Repository\BatchIteratorTrait;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

/**
 * Doctrine repository for Oro\Bundle\CustomerBundle\Entity\Customer entity
 */
class CustomerRepository extends EntityRepository implements BatchIteratorInterface
{
    use BatchIteratorTrait;

    /**
     * @param string $name
     *
     * @return null|Customer
     */
    public function findOneByName($name)
    {
        return $this->findOneBy(['name' => $name]);
    }

    /**
     * @param CustomerGroup $customerGroup
     * @return array|int[]
     */
    public function getIdsByCustomerGroup(CustomerGroup $customerGroup)
    {
        $qb = $this->createQueryBuilder('customer');

        $result = $qb->select('customer.id')
            ->where($qb->expr()->eq('customer.group', ':customerGroup'))
            ->setParameter('customerGroup', $customerGroup)
            ->getQuery()
            ->getScalarResult();

        return array_column($result, 'id');
    }

    /**
     * @param int $customerId
     * @param AclHelper $aclHelper
     * @return array
     */
    public function getChildrenIds($customerId, AclHelper $aclHelper = null)
    {
        $qb = $this->createQueryBuilder('customer');
        $qb->select('customer.id as customer_id')
            ->where($qb->expr()->eq('IDENTITY(customer.parent)', ':parent'))
            ->setParameter('parent', $customerId);

        if ($aclHelper) {
            $query = $aclHelper->apply($qb);
        } else {
            $query = $qb->getQuery();
        }

        $result = array_map(
            function ($item) {
                return $item['customer_id'];
            },
            $query->getArrayResult()
        );
        $children = $result;

        if ($result) {
            foreach ($result as $childId) {
                $children = array_merge($children, $this->getChildrenIds($childId, $aclHelper));
            }
        }

        return $children;
    }
}
