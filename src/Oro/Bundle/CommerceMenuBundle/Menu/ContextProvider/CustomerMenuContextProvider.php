<?php

namespace Oro\Bundle\CommerceMenuBundle\Menu\ContextProvider;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Provider\ScopeCustomerCriteriaProvider;

class CustomerMenuContextProvider implements CustomerMenuContextProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function getContexts(Customer $customer)
    {
        return [
            [
                ScopeCustomerCriteriaProvider::ACCOUNT => $customer->getId(),
            ]
        ];
    }
}
