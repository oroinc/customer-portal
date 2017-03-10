<?php

namespace Oro\Bundle\CommerceMenuBundle\Menu\ContextProvider;

use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\CustomerBundle\Provider\ScopeCustomerGroupCriteriaProvider;

class CustomerGroupMenuContextProvider implements CustomerGroupMenuContextProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function getContexts(CustomerGroup $customerGroup)
    {
        return [
            [
                ScopeCustomerGroupCriteriaProvider::FIELD_NAME => $customerGroup->getId()
            ]
        ];
    }
}
