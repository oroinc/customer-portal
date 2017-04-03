<?php

namespace Oro\Bundle\CustomerBundle\Layout\DataProvider;

use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\UserBundle\Provider\RolePrivilegeCapabilityProvider;
use Oro\Bundle\UserBundle\Provider\RolePrivilegeCategoryProvider;

class FrontendCustomerUserRoleCapabilitySetOptionsProvider implements FrontendCustomerUserRoleOptionsProviderInterface
{
    /**
     * @var array
     */
    private $options = [];

    /**
     * @var RolePrivilegeCapabilityProvider
     */
    private $capabilityProvider;

    /**
     * @var RolePrivilegeCategoryProvider
     */
    private $categoryProvider;

    /**
     * @param RolePrivilegeCapabilityProvider $capabilityProvider
     * @param RolePrivilegeCategoryProvider   $categoryProvider
     */
    public function __construct(
        RolePrivilegeCapabilityProvider $capabilityProvider,
        RolePrivilegeCategoryProvider $categoryProvider
    ) {
        $this->capabilityProvider = $capabilityProvider;
        $this->categoryProvider = $categoryProvider;
    }

    /**
     * @param CustomerUserRole $customerUserRole
     *
     * @return array
     */
    public function getOptions(CustomerUserRole $customerUserRole)
    {
        if (!array_key_exists('capabilitySetOptions', $this->options)) {
            $this->options['capabilitySetOptions'] = [
                'data' => $this->capabilityProvider->getCapabilities($customerUserRole),
                'tabIds' => $this->categoryProvider->getTabList()
            ];
        }

        return $this->options['capabilitySetOptions'];
    }
}
