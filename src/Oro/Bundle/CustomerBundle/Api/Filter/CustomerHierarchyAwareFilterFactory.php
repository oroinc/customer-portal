<?php

namespace Oro\Bundle\CustomerBundle\Api\Filter;

use Oro\Bundle\SecurityBundle\Owner\OwnerTreeProviderInterface;

/**
 * Factory to create CustomersHierarchyAwareFilter instances.
 */
class CustomerHierarchyAwareFilterFactory
{
    public function __construct(private OwnerTreeProviderInterface $customerTreeProvider)
    {
    }

    public function createFilter(string $dataType): CustomerHierarchyAwareFilter
    {
        $filter = new CustomerHierarchyAwareFilter($dataType);
        $filter->setCustomerTreeProvider($this->customerTreeProvider);
        $filter->setDescription('Filter customers aware of hierarchy.');

        return $filter;
    }
}
