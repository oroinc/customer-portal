<?php

namespace Oro\Bundle\CommerceMenuBundle\Menu\ContextProvider;

use Oro\Bundle\CustomerBundle\Entity\Customer;

interface CustomerMenuContextProviderInterface
{
    /**
     * @param Customer $customer
     * @return array
     */
    public function getContexts(Customer $customer);
}
