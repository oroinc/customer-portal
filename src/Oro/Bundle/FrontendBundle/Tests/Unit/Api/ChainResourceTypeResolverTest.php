<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Api;

use Oro\Bundle\ApiBundle\Request\RequestType;
use Oro\Bundle\ApiBundle\Util\RequestExpressionMatcher;
use Oro\Bundle\FrontendBundle\Api\ChainResourceTypeResolver;
use Oro\Bundle\FrontendBundle\Api\ResourceTypeResolverInterface;
use Oro\Component\Testing\Unit\TestContainerBuilder;

class ChainResourceTypeResolverTest extends \PHPUnit\Framework\TestCase
{
    private const TEST_ROUTE = 'test_route';

    /** @var ResourceTypeResolverInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $resolver1;

    /** @var ResourceTypeResolverInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $resolver2;

    /** @var ResourceTypeResolverInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $resolver3;

    /** @var ResourceTypeResolverInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $resolver4;

    /** @var ResourceTypeResolverInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $resolver5;

    /** @var ChainResourceTypeResolver */
    private $chainResolver;

    protected function setUp(): void
    {
        $this->resolver1 = $this->createMock(ResourceTypeResolverInterface::class);
        $this->resolver2 = $this->createMock(ResourceTypeResolverInterface::class);
        $this->resolver3 = $this->createMock(ResourceTypeResolverInterface::class);
        $this->resolver4 = $this->createMock(ResourceTypeResolverInterface::class);
        $this->resolver5 = $this->createMock(ResourceTypeResolverInterface::class);

        $container = TestContainerBuilder::create()
            ->add('resolver1', $this->resolver1)
            ->add('resolver2', $this->resolver2)
            ->add('resolver3', $this->resolver3)
            ->add('resolver4', $this->resolver4)
            ->add('resolver5', $this->resolver5)
            ->getContainer($this);

        $this->chainResolver = new ChainResourceTypeResolver(
            [
                ['resolver1', null, null],
                ['resolver2', self::TEST_ROUTE, 'another'],
                ['resolver3', null, 'another|test'],
                ['resolver4', 'another_route', 'another|test'],
                ['resolver5', self::TEST_ROUTE, 'another|test']
            ],
            $container,
            new RequestExpressionMatcher()
        );
    }

    public function testResolveType()
    {
        $routeParameters = ['key' => 'val'];
        $requestType = new RequestType(['test']);
        $resolvedType = 'test';

        $this->resolver1->expects(self::once())
            ->method('resolveType')
            ->with(self::TEST_ROUTE, $routeParameters, $requestType)
            ->willReturn(null);
        $this->resolver2->expects(self::never())
            ->method('resolveType');
        $this->resolver3->expects(self::once())
            ->method('resolveType')
            ->with(self::TEST_ROUTE, $routeParameters, $requestType)
            ->willReturn($resolvedType);
        $this->resolver4->expects(self::never())
            ->method('resolveType');
        $this->resolver5->expects(self::never())
            ->method('resolveType');

        self::assertEquals(
            $resolvedType,
            $this->chainResolver->resolveType(self::TEST_ROUTE, $routeParameters, $requestType)
        );
    }

    public function testResolveTypeWhenAllResolversReturnNull()
    {
        $routeParameters = ['key' => 'val'];
        $requestType = new RequestType(['test']);

        $this->resolver1->expects(self::once())
            ->method('resolveType')
            ->with(self::TEST_ROUTE, $routeParameters, $requestType)
            ->willReturn(null);
        $this->resolver2->expects(self::never())
            ->method('resolveType');
        $this->resolver3->expects(self::once())
            ->method('resolveType')
            ->with(self::TEST_ROUTE, $routeParameters, $requestType)
            ->willReturn(null);
        $this->resolver4->expects(self::never())
            ->method('resolveType');
        $this->resolver5->expects(self::once())
            ->method('resolveType')
            ->with(self::TEST_ROUTE, $routeParameters, $requestType)
            ->willReturn(null);

        self::assertNull(
            $this->chainResolver->resolveType(self::TEST_ROUTE, $routeParameters, $requestType)
        );
    }
}
