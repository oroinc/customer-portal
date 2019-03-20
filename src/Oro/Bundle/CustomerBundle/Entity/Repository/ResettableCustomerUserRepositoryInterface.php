<?php

namespace Oro\Bundle\CustomerBundle\Entity\Repository;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;

/**
 * Reset customer user for entity owned by given customer user.
 */
interface ResettableCustomerUserRepositoryInterface
{
    /**
     * @param CustomerUser $customerUser
     * @param array $updatedEntities
     */
    public function resetCustomerUser(CustomerUser $customerUser, array $updatedEntities = []);

    /**
     * @param CustomerUser $customerUser
     * @return int
     */
    public function getRelatedEntitiesCount(CustomerUser $customerUser): int;
}
