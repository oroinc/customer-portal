<?php

namespace Oro\Bundle\CustomerBundle\Api\Filter;

use Doctrine\Common\Collections\Expr\Expression;
use Oro\Bundle\ApiBundle\Filter\ComparisonFilter;
use Oro\Bundle\ApiBundle\Filter\FilterValue;
use Oro\Bundle\ApiBundle\Util\ConfigUtil;
use Oro\Bundle\SecurityBundle\Owner\OwnerTreeProviderInterface;

/**
 * A filter that resolves and applies customer hierarchy-aware filtering.
 *
 * This filter expands the provided customer IDs to include their hierarchical children
 * based on the customer hierarchy structure. It then applies the resulting IDs to the criteria.
 */
class CustomerHierarchyAwareFilter extends ComparisonFilter
{
    private OwnerTreeProviderInterface $customerTreeProvider;

    public function setCustomerTreeProvider(OwnerTreeProviderInterface $customerTreeProvider): void
    {
        $this->customerTreeProvider = $customerTreeProvider;
    }

    #[\Override]
    protected function createExpression(FilterValue $value = null): ?Expression
    {
        if (null === $value) {
            return null;
        }
        $field = $this->getField();
        if (!$field) {
            throw new \InvalidArgumentException('The field must not be empty.');
        }
        if (ConfigUtil::IGNORE_PROPERTY_PATH === $field) {
            return null;
        }

        $customerIds = (array)$value->getValue();
        $subordinateIds = [];
        foreach ($customerIds as $customerId) {
            $subordinateIds[] = $this->customerTreeProvider->getTree()->getSubordinateBusinessUnitIds($customerId);
        }
        $customerIds = array_unique(array_merge($customerIds, ...$subordinateIds));
        sort($customerIds);

        return $this->buildExpression($field, $value->getPath(), $value->getOperator(), $customerIds);
    }
}
