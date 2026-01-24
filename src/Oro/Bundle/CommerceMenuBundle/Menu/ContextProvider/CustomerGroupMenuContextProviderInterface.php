<?php

namespace Oro\Bundle\CommerceMenuBundle\Menu\ContextProvider;

use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;

/**
 * Defines the contract for providing menu context based on customer group.
 *
 * Implementations of this interface provide context information for menu rendering and condition
 * evaluation specific to a customer group, enabling group-specific menu customization.
 */
interface CustomerGroupMenuContextProviderInterface
{
    /**
     * @param CustomerGroup $customerGroup
     * @return array
     */
    public function getContexts(CustomerGroup $customerGroup);
}
