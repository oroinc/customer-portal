<?php

namespace Oro\Bundle\CustomerBundle\Layout\DataProvider;

use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\UserBundle\Provider\RolePrivilegeCapabilityProvider;
use Oro\Bundle\UserBundle\Provider\RolePrivilegeCategoryProvider;

/**
 * Provides ACL capabilities and identifiers of ACL tabbed categories for storefront customer user role.
 */
class FrontendCustomerUserRoleCapabilitySetOptionsProvider implements FrontendCustomerUserRoleOptionsProviderInterface
{
    /** @var RolePrivilegeCapabilityProvider */
    private $capabilityProvider;

    /** @var RolePrivilegeCategoryProvider */
    private $categoryProvider;

    /** @var array|null */
    private $options;

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
     * @param CustomerUserRole $role
     *
     * @return array
     */
    public function getOptions(CustomerUserRole $role): array
    {
        if (null === $this->options) {
            $this->options = [
                'data'   => $this->capabilityProvider->getCapabilities($role),
                'tabIds' => $this->categoryProvider->getTabIds()
            ];
        }

        return $this->options;
    }
}
