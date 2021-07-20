<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api\Frontend\DataFixtures;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Tests\Functional\Api\DataFixtures\LoadCustomerUserRoles;
use Oro\Bundle\FrontendBundle\Tests\Functional\Api\FrontendRestJsonApiTestCase;

/**
 * Creates the customer user entity with administrative permissions that can be used to test frontend REST API.
 */
class LoadAdminCustomerUserData extends LoadCustomerUserData
{
    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return array_merge(parent::getDependencies(), [LoadCustomerUserRoles::class]);
    }

    protected function initializeCustomerUser(CustomerUser $customerUser)
    {
        $customerUser
            ->setEmail(FrontendRestJsonApiTestCase::USER_NAME)
            ->addRole($this->getReference('admin'));
    }
}
