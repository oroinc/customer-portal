<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Api\Filter;

use Oro\Bundle\CustomerBundle\Api\Filter\CustomerHierarchyAwareFilter;
use Oro\Bundle\CustomerBundle\Api\Filter\CustomerHierarchyAwareFilterFactory;
use Oro\Bundle\SecurityBundle\Owner\OwnerTreeProviderInterface;
use Oro\Component\Testing\ReflectionUtil;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CustomerHierarchyAwareFilterFactoryTest extends TestCase
{
    private OwnerTreeProviderInterface&MockObject $ownerTreeProvider;
    private CustomerHierarchyAwareFilterFactory $factory;

    #[\Override]
    protected function setUp(): void
    {
        $this->ownerTreeProvider = $this->createMock(OwnerTreeProviderInterface::class);

        $this->factory = new CustomerHierarchyAwareFilterFactory($this->ownerTreeProvider);
    }

    public function testCreateFilter(): void
    {
        $filter = $this->factory->createFilter('integer');

        self::assertInstanceOf(CustomerHierarchyAwareFilter::class, $filter);
        self::assertEquals('Filter customers aware of hierarchy.', $filter->getDescription());
        self::assertSame(
            $this->ownerTreeProvider,
            ReflectionUtil::getPropertyValue($filter, 'customerTreeProvider')
        );
    }
}
