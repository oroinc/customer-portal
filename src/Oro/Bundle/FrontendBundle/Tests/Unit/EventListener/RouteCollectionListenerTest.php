<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\EventListener;

use Oro\Bundle\DistributionBundle\Event\RouteCollectionEvent;
use Oro\Bundle\FrontendBundle\EventListener\RouteCollectionListener;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class RouteCollectionListenerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testOnCollectionAutoload(string $prefix, RouteCollection $collection, array $expected)
    {
        $listener = new RouteCollectionListener($prefix);

        $event = new RouteCollectionEvent($collection);
        $listener->onCollectionAutoload($event);

        $this->assertEquals($expected, $event->getCollection()->getIterator()->getArrayCopy());
    }

    public function dataProvider(): array
    {
        return [
            'prefix is empty after trim' => [
                ' / ',
                $this->getCollection(['route1' => new Route('/route1')]),
                ['route1' => new Route('/route1')]
            ],
            'without frontend route' => [
                ' /prefix/ ',
                $this->getCollection(['route1' => new Route('/route1')]),
                ['route1' => new Route('/prefix/route1')]
            ],
            'contains prefix for resource' => [
                ' /prefix/ ',
                $this->getCollection(['route1' => new Route('/prefix/route1')]),
                ['route1' => new Route('/prefix/route1')]
            ],
            'contains prefix for resource without slash' => [
                ' /prefix/ ',
                $this->getCollection(['route1' => new Route('prefix/route1')]),
                ['route1' => new Route('/prefix/route1')]
            ],
            'contains prefix for resource inside the path with slash' => [
                ' /prefix/ ',
                $this->getCollection(['route1' => new Route('/route1/prefix')]),
                ['route1' => new Route('/prefix/route1/prefix')]
            ],
            'contains prefix for resource inside the path' => [
                ' /prefix/ ',
                $this->getCollection(['route1' => new Route('/route1-prefix')]),
                ['route1' => new Route('/prefix/route1-prefix')]
            ],
            'first chars of route path equal to prefix' => [
                'admin',
                $this->getCollection(['route1' => new Route('/administration/route1')]),
                ['route1' => new Route('/admin/administration/route1')]
            ],
            'frontend route skip prefix' => [
                ' /prefix/ ',
                $this->getCollection(
                    [
                        'route1' => new Route('/route1'),
                        'route2' => new Route('/prefix/route2-prefix'),
                        'frontend1' => (new Route('/frontend1'))->setOption('frontend', false),
                        'frontend2' => (new Route('/frontend2'))->setOption('frontend', true),
                        'frontend3' => (new Route('/frontend3-prefix'))->setOption('frontend', false),
                        'frontend4' => (new Route('/frontend4-prefix'))->setOption('frontend', true),
                    ]
                ),
                [
                    'route1' => new Route('/prefix/route1'),
                    'route2' => new Route('/prefix/route2-prefix'),
                    'frontend1' => (new Route('/prefix/frontend1'))->setOption('frontend', false),
                    'frontend2' => (new Route('/frontend2'))->setOption('frontend', true),
                    'frontend3' => (new Route('/prefix/frontend3-prefix'))->setOption('frontend', false),
                    'frontend4' => (new Route('/frontend4-prefix'))->setOption('frontend', true),
                ]
            ],
            'with override_path option' => [
                'prefix',
                $this->getCollection(
                    [
                        'route1' => (new Route('/path1'))
                            ->setOption('override_path', '/api/route1'),
                        'route2' => (new Route('path2'))
                            ->setOption('override_path', 'api/route1'),
                        'frontend_route1' => (new Route('/path1'))
                            ->addOptions(['frontend' =>true, 'override_path' => '/api/route1']),
                        'frontend_route2' => (new Route('path2'))
                            ->addOptions(['frontend' =>true, 'override_path' => 'api/route1']),
                    ]
                ),
                [
                    'route1' => (new Route('/prefix/path1'))
                        ->setOption('override_path', '/prefix/api/route1'),
                    'route2' => (new Route('/prefix/path2'))
                        ->setOption('override_path', 'prefix/api/route1'),
                    'frontend_route1' => (new Route('/path1'))
                        ->addOptions(['frontend' =>true, 'override_path' => '/api/route1']),
                    'frontend_route2' => (new Route('path2'))
                        ->addOptions(['frontend' =>true, 'override_path' => 'api/route1']),
                ]
            ],
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
