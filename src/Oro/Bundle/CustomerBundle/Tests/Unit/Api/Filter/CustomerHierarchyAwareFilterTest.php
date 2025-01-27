<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Api\Filter;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Oro\Bundle\ApiBundle\Filter\FilterOperator;
use Oro\Bundle\ApiBundle\Filter\FilterValue;
use Oro\Bundle\CustomerBundle\Api\Filter\CustomerHierarchyAwareFilter;
use Oro\Bundle\SecurityBundle\Owner\OwnerTreeInterface;
use Oro\Bundle\SecurityBundle\Owner\OwnerTreeProviderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CustomerHierarchyAwareFilterTest extends TestCase
{
    private OwnerTreeProviderInterface&MockObject $customerTreeProvider;
    private OwnerTreeInterface&MockObject $ownerTree;

    protected function setUp(): void
    {
        $this->customerTreeProvider = $this->createMock(OwnerTreeProviderInterface::class);
        $this->ownerTree = $this->createMock(OwnerTreeInterface::class);

        $this->customerTreeProvider->expects(self::any())
            ->method('getTree')
            ->willReturn($this->ownerTree);
    }

    public function testApplyWithEmptyFilterValue(): void
    {
        $filter = new CustomerHierarchyAwareFilter('integer');
        $filter->setCustomerTreeProvider($this->customerTreeProvider);
        $filter->setField('customer');

        $criteria = new Criteria();

        $filter->apply($criteria, null);

        self::assertNull($criteria->getWhereExpression());
    }

    public function testApplyWithHierarchyExpansion(): void
    {
        $filter = new CustomerHierarchyAwareFilter('integer');
        $filter->setCustomerTreeProvider($this->customerTreeProvider);
        $filter->setField('customer');

        $this->ownerTree->expects(self::once())
            ->method('getSubordinateBusinessUnitIds')
            ->willReturnCallback(function ($customerId) {
                $hierarchy = [
                    1 => [2, 3],
                    4 => [5, 6]
                ];

                return $hierarchy[$customerId] ?? [];
            });

        $filterValue = new FilterValue('path', [1], FilterOperator::EQ);

        $criteria = new Criteria();
        $filter->apply($criteria, $filterValue);

        $expectedExpression = new Comparison('customer', Comparison::IN, [1, 2, 3]);
        self::assertEquals($expectedExpression, $criteria->getWhereExpression());
    }

    public function testApplyWithoutHierarchy(): void
    {
        $filter = new CustomerHierarchyAwareFilter('integer');
        $filter->setCustomerTreeProvider($this->customerTreeProvider);
        $filter->setField('customer');

        $this->ownerTree->expects(self::once())
            ->method('getSubordinateBusinessUnitIds')
            ->willReturn([]);

        $filterValue = new FilterValue('path', [5], FilterOperator::EQ);

        $criteria = new Criteria();
        $filter->apply($criteria, $filterValue);

        $expectedExpression = new Comparison('customer', Comparison::IN, [5]);
        self::assertEquals($expectedExpression, $criteria->getWhereExpression());
    }

    public function testApplyWithMultipleCustomerIds(): void
    {
        $filter = new CustomerHierarchyAwareFilter('integer');
        $filter->setCustomerTreeProvider($this->customerTreeProvider);
        $filter->setField('customer');

        $this->ownerTree->expects(self::exactly(2))
            ->method('getSubordinateBusinessUnitIds')
            ->willReturnCallback(function ($customerId) {
                $hierarchy = [
                    1 => [2, 3],
                    4 => [5, 6]
                ];

                return $hierarchy[$customerId] ?? [];
            });

        $filterValue = new FilterValue('path', [1, 4], FilterOperator::EQ);

        $criteria = new Criteria();
        $filter->apply($criteria, $filterValue);

        $expectedExpression = new Comparison('customer', Comparison::IN, [1, 2, 3, 4, 5, 6]);
        self::assertEquals($expectedExpression, $criteria->getWhereExpression());
    }
}
