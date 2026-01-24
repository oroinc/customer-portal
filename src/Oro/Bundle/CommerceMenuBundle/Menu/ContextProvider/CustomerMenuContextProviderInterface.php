<?php

namespace Oro\Bundle\CommerceMenuBundle\Menu\ContextProvider;

use Oro\Bundle\CustomerBundle\Entity\Customer;

/**
 * Defines the contract for providing menu context based on customer.
 *
 * Implementations of this interface provide context information for menu rendering and condition
 * evaluation specific to a customer, enabling customer-specific menu customization.
 */
interface CustomerMenuContextProviderInterface
{
    /**
     * @param Customer $customer
     * @return array
     */
    public function getContexts(Customer $customer);
}
