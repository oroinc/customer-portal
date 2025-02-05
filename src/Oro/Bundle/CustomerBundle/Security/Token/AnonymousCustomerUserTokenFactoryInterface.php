<?php

namespace Oro\Bundle\CustomerBundle\Security\Token;

use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

/**
 * An interface for factories to create anonymous customer visitor token.
 */
interface AnonymousCustomerUserTokenFactoryInterface
{
    public function create(
        CustomerVisitor $customerVisitor,
        Organization $organization,
        array $roles = []
    ): AnonymousCustomerUserToken;

    public function createApi(
        CustomerVisitor $customerVisitor,
        Organization $organization,
        array $roles = []
    ): ApiAnonymousCustomerUserToken;
}
