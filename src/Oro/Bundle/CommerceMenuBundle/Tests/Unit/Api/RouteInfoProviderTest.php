<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Api;

use Oro\Bundle\ApiBundle\Request\RequestType;
use Oro\Bundle\CommerceMenuBundle\Api\RouteInfoProvider;
use Oro\Bundle\FrontendBundle\Api\ResourceApiUrlResolverInterface;
use Oro\Bundle\FrontendBundle\Api\ResourceTypeResolverInterface;
use Oro\Bundle\RedirectBundle\Api\Model\Route;
use Oro\Bundle\RedirectBundle\Api\Repository\RouteRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RouteInfoProviderTest extends TestCase
{
    private RouteRepository&MockObject $routeRepository;
    private ResourceTypeResolverInterface&MockObject $resourceTypeResolver;
    private ResourceApiUrlResolverInterface&MockObject $apiUrlResolver;
    private RouteInfoProvider $provider;
    private RequestType $requestType;

    #[\Override]
    protected function setUp(): void
    {
        $this->routeRepository = $this->createMock(RouteRepository::class);
        $this->resourceTypeResolver = $this->createMock(ResourceTypeResolverInterface::class);
        $this->apiUrlResolver = $this->createMock(ResourceApiUrlResolverInterface::class);
        $this->provider = new RouteInfoProvider(
            $this->routeRepository,
            $this->resourceTypeResolver,
            $this->apiUrlResolver
        );
        $this->requestType = new RequestType(['frontend', 'rest', 'json_api']);
    }

    public function testGetRoutesInfoWithValidUris(): void
    {
        $uris = ['/product', '/category/electronics'];
        $route1 = $this->createRoute('/product', 'oro_product_frontend_product_index', [], false);
        $route2 = $this->createRoute(
            '/category/electronics',
            'oro_catalog_frontend_product_index',
            ['categoryId' => 1],
            true
        );

        $this->routeRepository->expects(self::exactly(2))
            ->method('findRoute')
            ->willReturnMap([
                [':product', $route1],
                [':category:electronics', $route2]
            ]);

        $this->resourceTypeResolver->expects(self::exactly(2))
            ->method('resolveType')
            ->willReturnMap([
                ['oro_product_frontend_product_index', [], $this->requestType, 'products'],
                ['oro_catalog_frontend_product_index', ['categoryId' => 1], $this->requestType, 'products']
            ]);

        $this->apiUrlResolver->expects(self::exactly(2))
            ->method('resolveApiUrl')
            ->willReturnMap([
                ['oro_product_frontend_product_index', [], 'products', $this->requestType, '/api/products'],
                [
                    'oro_catalog_frontend_product_index',
                    ['categoryId' => 1],
                    'products',
                    $this->requestType,
                    '/api/products?categoryId=1'
                ]
            ]);

        $result = $this->provider->getRoutesInfo($uris, $this->requestType);

        self::assertCount(2, $result);
        self::assertArrayHasKey('/product', $result);
        self::assertArrayHasKey('/category/electronics', $result);
        self::assertEquals([
            'isSlug' => false,
            'redirectUrl' => null,
            'redirectStatusCode' => null,
            'resourceType' => 'products',
            'apiUrl' => '/api/products'
        ], $result['/product']);
        self::assertEquals([
            'isSlug' => true,
            'redirectUrl' => null,
            'redirectStatusCode' => null,
            'resourceType' => 'products',
            'apiUrl' => '/api/products?categoryId=1'
        ], $result['/category/electronics']);
    }

    public function testGetRoutesInfoWithInvalidUri(): void
    {
        $uris = ['invalid-uri', '/valid-uri'];
        $route = $this->createRoute('/valid-uri', 'oro_product_frontend_product_index', [], false);

        $this->routeRepository->expects(self::once())
            ->method('findRoute')
            ->with(':valid-uri')
            ->willReturn($route);

        $this->resourceTypeResolver->expects(self::once())
            ->method('resolveType')
            ->willReturn('products');

        $this->apiUrlResolver->expects(self::once())
            ->method('resolveApiUrl')
            ->willReturn('/api/products');

        $result = $this->provider->getRoutesInfo($uris, $this->requestType);

        self::assertCount(2, $result);
        self::assertNull($result['invalid-uri']);
        self::assertNotNull($result['/valid-uri']);
    }

    public function testGetRoutesInfoWithNotFoundRoute(): void
    {
        $uris = ['/non-existent'];

        $this->routeRepository->expects(self::once())
            ->method('findRoute')
            ->with(':non-existent')
            ->willReturn(null);

        $this->resourceTypeResolver->expects(self::never())
            ->method('resolveType');

        $this->apiUrlResolver->expects(self::never())
            ->method('resolveApiUrl');

        $result = $this->provider->getRoutesInfo($uris, $this->requestType);

        self::assertCount(1, $result);
        self::assertNull($result['/non-existent']);
    }

    public function testGetRoutesInfoWithUnknownResourceType(): void
    {
        $uris = ['/unknown-route'];
        $route = $this->createRoute('/unknown-route', 'unknown_route', [], false);

        $this->routeRepository->expects(self::once())
            ->method('findRoute')
            ->willReturn($route);

        $this->resourceTypeResolver->expects(self::once())
            ->method('resolveType')
            ->willReturn(null);

        $this->apiUrlResolver->expects(self::never())
            ->method('resolveApiUrl');

        $result = $this->provider->getRoutesInfo($uris, $this->requestType);

        self::assertCount(1, $result);
        self::assertEquals([
            'isSlug' => false,
            'redirectUrl' => null,
            'redirectStatusCode' => null,
            'resourceType' => 'unknown',
            'apiUrl' => null
        ], $result['/unknown-route']);
    }

    public function testGetRoutesInfoWithRedirect(): void
    {
        $uris = ['/old-url'];
        $route = $this->createRoute('/old-url', 'oro_product_frontend_product_index', [], false);
        $route->setRedirect('/new-url', 301);

        $this->routeRepository->expects(self::once())
            ->method('findRoute')
            ->willReturn($route);

        $this->resourceTypeResolver->expects(self::once())
            ->method('resolveType')
            ->willReturn('products');

        $this->apiUrlResolver->expects(self::once())
            ->method('resolveApiUrl')
            ->willReturn('/api/products');

        $result = $this->provider->getRoutesInfo($uris, $this->requestType);

        self::assertCount(1, $result);
        self::assertEquals([
            'isSlug' => false,
            'redirectUrl' => '/new-url',
            'redirectStatusCode' => 301,
            'resourceType' => 'products',
            'apiUrl' => '/api/products'
        ], $result['/old-url']);
    }

    public function testGetRoutesInfoWithDuplicateUris(): void
    {
        $uris = ['/product', '/product', '/category'];
        $route1 = $this->createRoute('/product', 'oro_product_frontend_product_index', [], false);
        $route2 = $this->createRoute(
            '/category',
            'oro_catalog_frontend_product_index',
            [],
            false
        );

        $this->routeRepository->expects(self::exactly(2))
            ->method('findRoute')
            ->willReturnMap([
                [':product', $route1],
                [':category', $route2]
            ]);

        $this->resourceTypeResolver->expects(self::exactly(2))
            ->method('resolveType')
            ->willReturn('products');

        $this->apiUrlResolver->expects(self::exactly(2))
            ->method('resolveApiUrl')
            ->willReturn('/api/products');

        $result = $this->provider->getRoutesInfo($uris, $this->requestType);

        self::assertCount(2, $result);
        self::assertArrayHasKey('/product', $result);
        self::assertArrayHasKey('/category', $result);
    }

    public function testGetRoutesInfoWithEmptyUris(): void
    {
        $result = $this->provider->getRoutesInfo([], $this->requestType);

        self::assertEmpty($result);
    }

    private function createRoute(string $url, string $routeName, array $routeParameters, bool $isSlug): Route
    {
        $routeId = str_replace('/', ':', $url);
        return new Route($routeId, $url, $routeName, $routeParameters, $isSlug);
    }
}
