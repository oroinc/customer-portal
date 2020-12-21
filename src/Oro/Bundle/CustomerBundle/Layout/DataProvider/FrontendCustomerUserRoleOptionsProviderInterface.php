<?php

namespace Oro\Bundle\CustomerBundle\Layout\DataProvider;

use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;

/**
 * Represents options provider for storefront customer user role.
 */
interface FrontendCustomerUserRoleOptionsProviderInterface
{
    /**
     * @param CustomerUserRole $role
     *
     * @return array
     */
    public function getOptions(CustomerUserRole $role): array;
}
