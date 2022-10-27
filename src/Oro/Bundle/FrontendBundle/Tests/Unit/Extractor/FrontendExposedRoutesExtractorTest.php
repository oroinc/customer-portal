<?php
declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Extractor;

use Oro\Bundle\FrontendBundle\Extractor\FrontendExposedRoutesExtractor;
use Oro\Component\Testing\TempDirExtension;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

class FrontendExposedRoutesExtractorTest extends \PHPUnit\Framework\TestCase
{
    use TempDirExtension;

    private string $cacheDir;

    /** @var FrontendExposedRoutesExtractor */
    private $extractor;

    protected function setUp(): void
    {
        $router = $this->createMock(RouterInterface::class);

        $routes = new RouteCollection();
        foreach ($this->routeProvider() as $data) {
            $routes->add($data['name'], $data['route']);
        }
        $router->expects(self::any())
            ->method('getRouteCollection')
            ->willReturn($routes);

        $this->cacheDir = $this->getTempDir('exposed_routes');

        $this->extractor = new FrontendExposedRoutesExtractor($router, ['route_.*'], $this->cacheDir);
    }

    public function testGetRoutes(): void
    {
        // comparing route names
        self::assertSame(
            array_column(array_filter($this->routeProvider(), static fn ($d) => $d['should_be_exposed']), 'name'),
            array_keys($this->extractor->getRoutes()->all())
        );
    }

    /**
     * @dataProvider routeProvider
     */
    public function testIsRouteExposed(Route $route, string $name, bool $shouldBeExposed): void
    {
        self::assertEquals($shouldBeExposed, $this->extractor->isRouteExposed($route, $name));
    }

    public function routeProvider(): array
    {
        return [
            [
                'route' => new Route('route_1', [], [], ['frontend' => true, 'expose' => true]),
                'name' => 'route_1',
                'should_be_exposed' => true
            ],
            [
                'route' => new Route('route_2', [], [], ['frontend' => false, 'expose' => true]),
                'name' => 'route_2',
                'should_be_exposed' => false
            ],
            [
                'route' => new Route('route_3', [], [], ['expose' => true]),
                'name' => 'route_3',
                'should_be_exposed' => false
            ],
            [
                'route' => new Route('route_5', [], [], ['frontend' => true]),
                'name' => 'route_5',
                'should_be_exposed' => true
            ],
            [
                'route' => new Route('route_6', [], [], ['frontend' => true, 'expose' => false]),
                'name' => 'route_6',
                'should_be_exposed' => true
            ]
        ];
    }

    public function testGetCachePath(): void
    {
        self::assertEquals($this->cacheDir . '/fosJsRouting/frontend_data.json', $this->extractor->getCachePath(''));
    }
}
