<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Api;

use Oro\Bundle\ApiBundle\Request\RequestType;
use Oro\Bundle\ApiBundle\Util\RequestExpressionMatcher;
use Oro\Bundle\FrontendBundle\Api\ChainResourceApiUrlResolver;
use Oro\Bundle\FrontendBundle\Api\ResourceApiUrlResolverInterface;
use Oro\Component\Testing\Unit\TestContainerBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ChainResourceApiUrlResolverTest extends TestCase
{
    private const TEST_ROUTE = 'test_route';

    private ResourceApiUrlResolverInterface&MockObject $resolver1;
    private ResourceApiUrlResolverInterface&MockObject $resolver2;
    private ResourceApiUrlResolverInterface&MockObject $resolver3;
    private ResourceApiUrlResolverInterface&MockObject $resolver4;
    private ResourceApiUrlResolverInterface&MockObject $resolver5;
    private ChainResourceApiUrlResolver $chainResolver;

    #[\Override]
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

    public function testResolveApiUrl(): void
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

    public function testResolveApiUrlWhenAllResolversReturnNull(): void
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
