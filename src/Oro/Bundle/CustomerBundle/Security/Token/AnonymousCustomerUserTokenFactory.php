<?php

namespace Oro\Bundle\CustomerBundle\Security\Token;

use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

/**
 * The factory to create anonymous customer user token.
 */
class AnonymousCustomerUserTokenFactory implements AnonymousCustomerUserTokenFactoryInterface
{
    #[\Override]
    public function create(
        CustomerVisitor $customerVisitor,
        Organization $organization,
        array $roles = []
    ): AnonymousCustomerUserToken {
        return new AnonymousCustomerUserToken(
            $customerVisitor,
            $roles,
            $organization
        );
    }

    #[\Override]
    public function createApi(
        CustomerVisitor $customerVisitor,
        Organization $organization,
        array $roles = []
    ): ApiAnonymousCustomerUserToken {
        return new ApiAnonymousCustomerUserToken(
            $customerVisitor,
            $roles,
            $organization
        );
    }
}
