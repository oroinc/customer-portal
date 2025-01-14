<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Api\Filter;

use Oro\Bundle\CustomerBundle\Api\Filter\CustomerHierarchyAwareFilter;
use Oro\Bundle\CustomerBundle\Api\Filter\CustomerHierarchyAwareFilterFactory;
use Oro\Bundle\SecurityBundle\Owner\OwnerTreeProviderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CustomerHierarchyAwareFilterFactoryTest extends TestCase
{
    private CustomerHierarchyAwareFilterFactory $factory;
    private OwnerTreeProviderInterface&MockObject $ownerTreeProvider;

    protected function setUp(): void
    {
        $this->ownerTreeProvider = $this->createMock(OwnerTreeProviderInterface::class);
        $this->factory = new CustomerHierarchyAwareFilterFactory($this->ownerTreeProvider);
    }

    public function testCreateFilter(): void
    {
        $dataType = 'integer';
        $filter = $this->factory->createFilter($dataType);

        static::assertInstanceOf(CustomerHierarchyAwareFilter::class, $filter);
        static::assertEquals('Filter customers aware of hierarchy.', $filter->getDescription());
        static::assertSame($this->ownerTreeProvider, $this->getCustomerTreeProvider($filter));
    }

    private function getCustomerTreeProvider(CustomerHierarchyAwareFilter $filter): OwnerTreeProviderInterface
    {
        $reflection = new \ReflectionClass($filter);
        $property = $reflection->getProperty('customerTreeProvider');

        return $property->getValue($filter);
    }
}
