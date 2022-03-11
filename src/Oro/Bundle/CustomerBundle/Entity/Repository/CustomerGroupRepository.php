<?php

namespace Oro\Bundle\CustomerBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\BatchBundle\ORM\Query\BufferedIdentityQueryResultIterator;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;

/**
 * ORM repository for CustomerGroup entity.
 */
class CustomerGroupRepository extends EntityRepository
{
    /**
     * @param string $name
     *
     * @return null|CustomerGroup
     */
    public function findOneByName($name)
    {
        return $this->findOneBy(['name' => $name]);
    }

    public function getCustomerGroupsNotInList(array $skipIds): \Iterator
    {
        $qb = $this->createQueryBuilder('cg');
        if ($skipIds) {
            $qb->where($qb->expr()->notIn('cg.id', ':ids'))
                ->setParameter('ids', $skipIds);
        }

        return new BufferedIdentityQueryResultIterator($qb);
    }
}
