<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\EventListener;

use Oro\Bundle\DistributionBundle\Event\RouteCollectionEvent;
use Oro\Bundle\FrontendBundle\EventListener\FrontendRouteCollectionListener;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class FrontendRouteCollectionListenerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testOnCollectionAutoload(RouteCollection $collection, array $expected)
    {
        $listener = new FrontendRouteCollectionListener(['route_should_be_frontend']);

        $event = new RouteCollectionEvent($collection);
        $listener->onCollectionAutoload($event);

        $this->assertEquals($expected, $event->getCollection()->getIterator()->getArrayCopy());
    }

    public function dataProvider(): array
    {
        return [
            [
                $this->getCollection(['route_should_be_frontend' => new Route('/route1')]),
                ['route_should_be_frontend' => (new Route('/route1'))->setOption('frontend', true)]
            ],
            [
                $this->getCollection(['route_should_not_be_frontend' => new Route('/route2')]),
                ['route_should_not_be_frontend' => new Route('/route2')]
            ]

        ];
    }

    private function getCollection(array $routes): RouteCollection
    {
        $collection = new RouteCollection();
        foreach ($routes as $routeName => $route) {
            $collection->add($routeName, $route);
        }

        return $collection;
    }
}
