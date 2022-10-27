<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Api;

use Oro\Bundle\ApiBundle\Request\RequestType;
use Oro\Bundle\ApiBundle\Util\RequestExpressionMatcher;
use Oro\Bundle\FrontendBundle\Api\ChainResourceApiUrlResolver;
use Oro\Bundle\FrontendBundle\Api\ResourceApiUrlResolverInterface;
use Oro\Component\Testing\Unit\TestContainerBuilder;

class ChainResourceApiUrlResolverTest extends \PHPUnit\Framework\TestCase
{
    private const TEST_ROUTE = 'test_route';

    /** @var ResourceApiUrlResolverInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $resolver1;

    /** @var ResourceApiUrlResolverInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $resolver2;

    /** @var ResourceApiUrlResolverInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $resolver3;

    /** @var ResourceApiUrlResolverInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $resolver4;

    /** @var ResourceApiUrlResolverInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $resolver5;

    /** @var ChainResourceApiUrlResolver */
    private $chainResolver;

    protected function setUp(): void
    {
        $this->resolver1 = $this->createMock(ResourceApiUrlResolverInterface::class);
        $this->resolver2 = $this->createMock(ResourceApiUrlResolverInterface::class);
        $this->resolver3 = $this->createMock(ResourceApiUrlResolverInterface::class);
        $this->resolver4 = $this->createMock(ResourceApiUrlResolverInterface::class);
        $this->resolver5 = $this->createMock(ResourceApiUrlResolverInterface::class);

        $container = TestContainerBuilder::create()
            ->add('resolver1', $this->resolver1)
            ->add('resolver2', $this->resolver2)
            ->add('resolver3', $this->resolver3)
            ->add('resolver4', $this->resolver4)
            ->add('resolver5', $this->resolver5)
            ->getContainer($this);

        $this->chainResolver = new ChainResourceApiUrlResolver(
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

    public function testResolveApiUrl()
    {
        $routeParameters = ['key' => 'val'];
        $resourceType = 'test_resource';
        $requestType = new RequestType(['test']);
        $resolvedUrl = 'test';

        $this->resolver1->expects(self::once())
            ->method('resolveApiUrl')
            ->with(self::TEST_ROUTE, $routeParameters, $resourceType, $requestType)
            ->willReturn(null);
        $this->resolver2->expects(self::never())
            ->method('resolveApiUrl');
        $this->resolver3->expects(self::once())
            ->method('resolveApiUrl')
            ->with(self::TEST_ROUTE, $routeParameters, $resourceType, $requestType)
            ->willReturn($resolvedUrl);
        $this->resolver4->expects(self::never())
            ->method('resolveApiUrl');
        $this->resolver5->expects(self::never())
            ->method('resolveApiUrl');

        self::assertEquals(
            $resolvedUrl,
            $this->chainResolver->resolveApiUrl(self::TEST_ROUTE, $routeParameters, $resourceType, $requestType)
        );
    }

    public function testResolveApiUrlWhenAllResolversReturnNull()
    {
        $routeParameters = ['key' => 'val'];
        $resourceType = 'test_resource';
        $requestType = new RequestType(['test']);

        $this->resolver1->expects(self::once())
            ->method('resolveApiUrl')
            ->with(self::TEST_ROUTE, $routeParameters, $resourceType, $requestType)
            ->willReturn(null);
        $this->resolver2->expects(self::never())
            ->method('resolveApiUrl');
        $this->resolver3->expects(self::once())
            ->method('resolveApiUrl')
            ->with(self::TEST_ROUTE, $routeParameters, $resourceType, $requestType)
            ->willReturn(null);
        $this->resolver4->expects(self::never())
            ->method('resolveApiUrl');
        $this->resolver5->expects(self::once())
            ->method('resolveApiUrl')
            ->with(self::TEST_ROUTE, $routeParameters, $resourceType, $requestType)
            ->willReturn(null);

        self::assertNull(
            $this->chainResolver->resolveApiUrl(self::TEST_ROUTE, $routeParameters, $resourceType, $requestType)
        );
    }
}
