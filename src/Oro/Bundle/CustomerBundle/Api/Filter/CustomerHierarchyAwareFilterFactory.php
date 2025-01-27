<?php

namespace Oro\Bundle\CustomerBundle\Api\Filter;

use Oro\Bundle\SecurityBundle\Owner\OwnerTreeProviderInterface;

/**
 * The factory to create {@see CustomerHierarchyAwareFilter}.
 */
class CustomerHierarchyAwareFilterFactory
{
    public function __construct(
        private readonly OwnerTreeProviderInterface $customerTreeProvider
    ) {
    }

    public function createFilter(string $dataType): CustomerHierarchyAwareFilter
    {
        $filter = new CustomerHierarchyAwareFilter($dataType, 'Filter customers aware of hierarchy.');
        $filter->setCustomerTreeProvider($this->customerTreeProvider);

        return $filter;
    }
}
