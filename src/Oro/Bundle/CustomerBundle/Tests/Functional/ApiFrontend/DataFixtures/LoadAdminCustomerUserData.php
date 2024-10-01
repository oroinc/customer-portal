<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\ApiFrontend\DataFixtures;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Tests\Functional\Api\DataFixtures\LoadCustomerUserRoles;

/**
 * Creates the customer user entity with administrative permissions that can be used to test frontend REST API.
 */
class LoadAdminCustomerUserData extends LoadCustomerUserData
{
    #[\Override]
    public function getDependencies()
    {
        return array_merge(parent::getDependencies(), [LoadCustomerUserRoles::class]);
    }

    #[\Override]
    protected function initializeCustomerUser(CustomerUser $customerUser)
    {
        $customerUser
            ->setEmail(static::USER_NAME)
            ->addUserRole($this->getReference('admin'));
    }
}
