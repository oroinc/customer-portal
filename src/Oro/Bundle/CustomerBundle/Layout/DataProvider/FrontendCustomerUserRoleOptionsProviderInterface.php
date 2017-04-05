<?php

namespace Oro\Bundle\CustomerBundle\Layout\DataProvider;

use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;

interface FrontendCustomerUserRoleOptionsProviderInterface
{
    /**
     * @param CustomerUserRole $role
     *
     * @return array
     */
    public function getOptions(CustomerUserRole $role);
}
