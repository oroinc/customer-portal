<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Api\Processor;

use Oro\Bundle\ApiBundle\Processor\CustomizeLoadedData\CustomizeLoadedDataContext;
use Oro\Bundle\ApiBundle\Request\RequestType;
use Oro\Bundle\CommerceMenuBundle\Api\Processor\ComputeMenuItemResourceProcessor;
use Oro\Bundle\CommerceMenuBundle\Api\RouteInfoProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ComputeMenuItemResourceProcessorTest extends TestCase
{
    private RouteInfoProvider&MockObject $routeInfoProvider;
    private ComputeMenuItemResourceProcessor $processor;
    private CustomizeLoadedDataContext $context;

    #[\Override]
    protected function setUp(): void
    {
        $this->routeInfoProvider = $this->createMock(RouteInfoProvider::class);
        $this->processor = new ComputeMenuItemResourceProcessor($this->routeInfoProvider);
        $this->context = new CustomizeLoadedDataContext();
        $this->context->getRequestType()->set(new RequestType(['frontend', 'rest', 'json_api']));
    }

    public function testProcessWithMenuItemsWithUris(): void
    {
        $menuItems = [
            ['uri' => '/product', 'name' => 'product'],
            ['uri' => '/category', 'name' => 'category'],
            ['uri' => '', 'name' => 'empty-uri'],
            ['name' => 'no-uri']
        ];

        $routesInfo = [
            '/product' => [
                'isSlug' => false,
                'redirectUrl' => null,
                'redirectStatusCode' => null,
                'resourceType' => 'products',
                'apiUrl' => '/api/products'
            ],
            '/category' => [
                'isSlug' => true,
                'redirectUrl' => null,
                'redirectStatusCode' => null,
                'resourceType' => 'categories',
                'apiUrl' => '/api/categories'
            ]
        ];

        $this->context->setData($menuItems);

        $this->routeInfoProvider->expects(self::once())
            ->method('getRoutesInfo')
            ->with(['/product', '/category'], $this->context->getRequestType())
            ->willReturn($routesInfo);

        $this->processor->process($this->context);

        $result = $this->context->getData();
        self::assertArrayHasKey('resource', $result[0]);
        self::assertEquals($routesInfo['/product'], $result[0]['resource']);
        self::assertArrayHasKey('resource', $result[1]);
        self::assertEquals($routesInfo['/category'], $result[1]['resource']);
        self::assertArrayHasKey('resource', $result[2]);
        self::assertNull($result[2]['resource']);
        self::assertArrayNotHasKey('resource', $result[3]);
    }

    public function testProcessWithEmptyUris(): void
    {
        $menuItems = [
            ['uri' => '', 'name' => 'empty-uri'],
            ['name' => 'no-uri']
        ];

        $this->context->setData($menuItems);

        $this->routeInfoProvider->expects(self::never())
            ->method('getRoutesInfo');

        $this->processor->process($this->context);

        $result = $this->context->getData();
        self::assertArrayNotHasKey('resource', $result[0]);
        self::assertArrayNotHasKey('resource', $result[1]);
    }

    public function testProcessWithNullRouteInfo(): void
    {
        $menuItems = [
            ['uri' => '/non-existent', 'name' => 'non-existent']
        ];

        $routesInfo = [
            '/non-existent' => null
        ];

        $this->context->setData($menuItems);

        $this->routeInfoProvider->expects(self::once())
            ->method('getRoutesInfo')
            ->with(['/non-existent'], $this->context->getRequestType())
            ->willReturn($routesInfo);

        $this->processor->process($this->context);

        $result = $this->context->getData();
        self::assertArrayHasKey('resource', $result[0]);
        self::assertNull($result[0]['resource']);
    }

    public function testProcessWithDuplicateUris(): void
    {
        $menuItems = [
            ['uri' => '/product', 'name' => 'product1'],
            ['uri' => '/product', 'name' => 'product2']
        ];

        $routesInfo = [
            '/product' => [
                'isSlug' => false,
                'redirectUrl' => null,
                'redirectStatusCode' => null,
                'resourceType' => 'products',
                'apiUrl' => '/api/products'
            ]
        ];

        $this->context->setData($menuItems);

        $this->routeInfoProvider->expects(self::once())
            ->method('getRoutesInfo')
            ->with(['/product', '/product'], $this->context->getRequestType())
            ->willReturn($routesInfo);

        $this->processor->process($this->context);

        $result = $this->context->getData();
        self::assertEquals($routesInfo['/product'], $result[0]['resource']);
        self::assertEquals($routesInfo['/product'], $result[1]['resource']);
    }
}
