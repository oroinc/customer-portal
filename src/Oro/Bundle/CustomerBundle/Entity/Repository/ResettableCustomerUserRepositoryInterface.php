<?php

namespace Oro\Bundle\CustomerBundle\Entity\Repository;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;

/**
 * Reset customer user for entity owned by given customer user.
 */
interface ResettableCustomerUserRepositoryInterface
{
    public function resetCustomerUser(CustomerUser $customerUser, array $updatedEntities = []);

    public function getRelatedEntitiesCount(CustomerUser $customerUser): int;
}
