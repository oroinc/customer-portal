<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Extractor;

use Oro\Bundle\FrontendBundle\Extractor\FrontendExposedRoutesExtractor;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

class FrontendExposedRoutesExtractorTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testGetExposedRoutes()
    {
        /** @var RouterInterface|\PHPUnit_Framework_MockObject_MockObject $router */
        $router = $this->createMock(RouterInterface::class);
        $routesCollection = new RouteCollection();

        $frontendRoute = new Route('route_1', [], [], ['frontend' => true, 'expose' => true]);
        $routesCollection->add('route_1', $frontendRoute);
        $routesCollection->add('route_2', new Route('route_2', [], [], ['frontend' => false, 'expose' => true]));
        $routesCollection->add('route_3', new Route('route_3', [], [], ['expose' => true]));
        $routesCollection->add('route_4', new Route('route_4', [], [], ['frontend' => true, 'expose' => false]));

        $router->method('getRouteCollection')->willReturn($routesCollection);

        $extractor = new FrontendExposedRoutesExtractor($router, ['route_*'], '');
        $resultRoutes = $extractor->getExposedRoutes();
        $this->assertEquals(['route_1' => $frontendRoute], $resultRoutes);
    }

    public function testGetCachePath()
    {
        /** @var RouterInterface|\PHPUnit_Framework_MockObject_MockObject $router */
        $router = $this->createMock(RouterInterface::class);
        $extractor = new FrontendExposedRoutesExtractor($router, ['route_*'], '/tmp');
        $this->assertEquals('/tmp/fosJsRouting/frontend_data.json', $extractor->getCachePath(''));
    }
}
