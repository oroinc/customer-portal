<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Provider;

use Oro\Bundle\EntityConfigBundle\Provider\EntityUrlProviderInterface;
use Oro\Bundle\FrontendBundle\Provider\StorefrontEntityUrlProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Routing\RouterInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class StorefrontEntityUrlProviderTest extends \PHPUnit\Framework\TestCase
{
    private RouterInterface|MockObject $router;
    private StorefrontEntityUrlProvider $provider;
    private array $storefrontEntityRoutes;

    #[\Override]
    protected function setUp(): void
    {
        $this->router = $this->createMock(RouterInterface::class);

        $this->storefrontEntityRoutes = [
            \stdClass::class => [
                EntityUrlProviderInterface::ROUTE_INDEX => 'oro_frontend_stdclass_index',
                EntityUrlProviderInterface::ROUTE_VIEW => 'oro_frontend_stdclass_view',
                EntityUrlProviderInterface::ROUTE_UPDATE => 'oro_frontend_stdclass_update',
                EntityUrlProviderInterface::ROUTE_CREATE => 'oro_frontend_stdclass_create',
            ],
            'App\Entity\Product' => [
                EntityUrlProviderInterface::ROUTE_INDEX => 'oro_frontend_product_index',
                EntityUrlProviderInterface::ROUTE_VIEW => 'oro_frontend_product_view',
            ],
        ];

        $this->provider = new StorefrontEntityUrlProvider($this->router, $this->storefrontEntityRoutes);
    }

    public function testGetRouteWithStringEntityAndIndexRouteType(): void
    {
        $entityClass = \stdClass::class;
        $expectedRoute = 'oro_frontend_stdclass_index';

        $result = $this->provider->getRoute($entityClass, EntityUrlProviderInterface::ROUTE_INDEX);

        $this->assertEquals($expectedRoute, $result);
    }

    public function testGetRouteWithStringEntityAndViewRouteType(): void
    {
        $entityClass = \stdClass::class;
        $expectedRoute = 'oro_frontend_stdclass_view';

        $result = $this->provider->getRoute($entityClass, EntityUrlProviderInterface::ROUTE_VIEW);

        $this->assertEquals($expectedRoute, $result);
    }

    public function testGetRouteWithStringEntityAndUpdateRouteType(): void
    {
        $entityClass = \stdClass::class;
        $expectedRoute = 'oro_frontend_stdclass_update';

        $result = $this->provider->getRoute($entityClass, EntityUrlProviderInterface::ROUTE_UPDATE);

        $this->assertEquals($expectedRoute, $result);
    }

    public function testGetRouteWithStringEntityAndCreateRouteType(): void
    {
        $entityClass = \stdClass::class;
        $expectedRoute = 'oro_frontend_stdclass_create';

        $result = $this->provider->getRoute($entityClass, EntityUrlProviderInterface::ROUTE_CREATE);

        $this->assertEquals($expectedRoute, $result);
    }

    public function testGetRouteWithObjectEntity(): void
    {
        $entity = new \stdClass();
        $expectedRoute = 'oro_frontend_stdclass_view';

        $result = $this->provider->getRoute($entity, EntityUrlProviderInterface::ROUTE_VIEW);

        $this->assertEquals($expectedRoute, $result);
    }

    public function testGetRouteReturnsNullWhenEntityNotConfigured(): void
    {
        $entityClass = 'App\Entity\NotConfigured';

        $result = $this->provider->getRoute($entityClass, EntityUrlProviderInterface::ROUTE_INDEX);

        $this->assertNull($result);
    }

    public function testGetRouteReturnsNullWhenRouteTypeNotConfigured(): void
    {
        $entityClass = 'App\Entity\Product';

        $result = $this->provider->getRoute($entityClass, EntityUrlProviderInterface::ROUTE_UPDATE);

        $this->assertNull($result);
    }

    public function testGetRouteThrowsExceptionWhenEntityNotConfiguredAndStrictMode(): void
    {
        $entityClass = 'App\Entity\NotConfigured';

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('No route "index" found for entity "App\Entity\NotConfigured"');

        $this->provider->getRoute($entityClass, EntityUrlProviderInterface::ROUTE_INDEX, true);
    }

    public function testGetRouteThrowsExceptionWhenRouteTypeNotConfiguredAndStrictMode(): void
    {
        $entityClass = 'App\Entity\Product';

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('No route "update" found for entity "App\Entity\Product"');

        $this->provider->getRoute($entityClass, EntityUrlProviderInterface::ROUTE_UPDATE, true);
    }

    public function testGetRouteDefaultsToIndexRouteType(): void
    {
        $entityClass = \stdClass::class;
        $expectedRoute = 'oro_frontend_stdclass_index';

        $result = $this->provider->getRoute($entityClass);

        $this->assertEquals($expectedRoute, $result);
    }

    public function testGetIndexUrl(): void
    {
        $entityClass = \stdClass::class;
        $expectedUrl = '/frontend/stdclass';

        $this->router->expects($this->once())
            ->method('generate')
            ->with('oro_frontend_stdclass_index', [])
            ->willReturn($expectedUrl);

        $result = $this->provider->getIndexUrl($entityClass);

        $this->assertEquals($expectedUrl, $result);
    }

    public function testGetIndexUrlWithExtraParams(): void
    {
        $entityClass = \stdClass::class;
        $extraParams = ['filter' => 'active', 'sort' => 'name'];
        $expectedUrl = '/frontend/stdclass?filter=active&sort=name';

        $this->router->expects($this->once())
            ->method('generate')
            ->with('oro_frontend_stdclass_index', $extraParams)
            ->willReturn($expectedUrl);

        $result = $this->provider->getIndexUrl($entityClass, $extraParams);

        $this->assertEquals($expectedUrl, $result);
    }

    public function testGetViewUrl(): void
    {
        $entityClass = \stdClass::class;
        $entityId = 123;
        $expectedUrl = '/frontend/stdclass/123';

        $this->router->expects($this->once())
            ->method('generate')
            ->with('oro_frontend_stdclass_view', ['id' => $entityId])
            ->willReturn($expectedUrl);

        $result = $this->provider->getViewUrl($entityClass, $entityId);

        $this->assertEquals($expectedUrl, $result);
    }

    public function testGetViewUrlWithExtraParams(): void
    {
        $entityClass = \stdClass::class;
        $entityId = 456;
        $extraParams = ['tab' => 'details'];
        $expectedParams = ['tab' => 'details', 'id' => $entityId];
        $expectedUrl = '/frontend/stdclass/456?tab=details';

        $this->router->expects($this->once())
            ->method('generate')
            ->with('oro_frontend_stdclass_view', $expectedParams)
            ->willReturn($expectedUrl);

        $result = $this->provider->getViewUrl($entityClass, $entityId, $extraParams);

        $this->assertEquals($expectedUrl, $result);
    }

    public function testGetUpdateUrl(): void
    {
        $entityClass = \stdClass::class;
        $entityId = 789;
        $expectedUrl = '/frontend/stdclass/789/update';

        $this->router->expects($this->once())
            ->method('generate')
            ->with('oro_frontend_stdclass_update', ['id' => $entityId])
            ->willReturn($expectedUrl);

        $result = $this->provider->getUpdateUrl($entityClass, $entityId);

        $this->assertEquals($expectedUrl, $result);
    }

    public function testGetUpdateUrlWithExtraParams(): void
    {
        $entityClass = \stdClass::class;
        $entityId = 321;
        $extraParams = ['redirect' => 'list'];
        $expectedParams = ['redirect' => 'list', 'id' => $entityId];
        $expectedUrl = '/frontend/stdclass/321/update?redirect=list';

        $this->router->expects($this->once())
            ->method('generate')
            ->with('oro_frontend_stdclass_update', $expectedParams)
            ->willReturn($expectedUrl);

        $result = $this->provider->getUpdateUrl($entityClass, $entityId, $extraParams);

        $this->assertEquals($expectedUrl, $result);
    }

    public function testGetCreateUrl(): void
    {
        $entityClass = \stdClass::class;
        $expectedUrl = '/frontend/stdclass/create';

        $this->router->expects($this->once())
            ->method('generate')
            ->with('oro_frontend_stdclass_create', [])
            ->willReturn($expectedUrl);

        $result = $this->provider->getCreateUrl($entityClass);

        $this->assertEquals($expectedUrl, $result);
    }

    public function testGetCreateUrlWithExtraParams(): void
    {
        $entityClass = \stdClass::class;
        $extraParams = ['template' => 'default'];
        $expectedUrl = '/frontend/stdclass/create?template=default';

        $this->router->expects($this->once())
            ->method('generate')
            ->with('oro_frontend_stdclass_create', $extraParams)
            ->willReturn($expectedUrl);

        $result = $this->provider->getCreateUrl($entityClass, $extraParams);

        $this->assertEquals($expectedUrl, $result);
    }

    public function testGetIndexUrlReturnsNullWhenRouteNotConfigured(): void
    {
        $entityClass = 'App\Entity\NotConfigured';

        $this->router->expects($this->never())
            ->method('generate');

        $result = $this->provider->getIndexUrl($entityClass);

        $this->assertNull($result);
    }

    public function testGetViewUrlReturnsNullWhenRouteNotConfigured(): void
    {
        $entityClass = 'App\Entity\NotConfigured';
        $entityId = 123;

        $this->router->expects($this->never())
            ->method('generate');

        $result = $this->provider->getViewUrl($entityClass, $entityId);

        $this->assertNull($result);
    }

    public function testGetUpdateUrlReturnsNullWhenRouteNotConfigured(): void
    {
        $entityClass = 'App\Entity\Product';
        $entityId = 456;

        $this->router->expects($this->never())
            ->method('generate');

        $result = $this->provider->getUpdateUrl($entityClass, $entityId);

        $this->assertNull($result);
    }

    public function testGetCreateUrlReturnsNullWhenRouteNotConfigured(): void
    {
        $entityClass = 'App\Entity\Product';

        $this->router->expects($this->never())
            ->method('generate');

        $result = $this->provider->getCreateUrl($entityClass);

        $this->assertNull($result);
    }

    public function testGetRouteWithEmptyStorefrontEntityRoutes(): void
    {
        $provider = new StorefrontEntityUrlProvider($this->router, []);
        $entityClass = \stdClass::class;

        $result = $provider->getRoute($entityClass, EntityUrlProviderInterface::ROUTE_INDEX);

        $this->assertNull($result);
    }
}
