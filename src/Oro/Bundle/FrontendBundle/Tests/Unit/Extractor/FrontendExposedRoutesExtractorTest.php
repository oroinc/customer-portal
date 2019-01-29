<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Extractor;

use Oro\Bundle\FrontendBundle\Extractor\FrontendExposedRoutesExtractor;
use Oro\Component\Testing\TempDirExtension;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;

class FrontendExposedRoutesExtractorTest extends \PHPUnit\Framework\TestCase
{
    use TempDirExtension;

    /** @var FrontendExposedRoutesExtractor */
    private $extractor;

    /** @var string */
    private $cacheDir;

    public function setUp()
    {
        /** @var RouterInterface|\PHPUnit\Framework\MockObject\MockObject $router */
        $router = $this->createMock(RouterInterface::class);

        $this->cacheDir = $this->getTempDir('exposed_routes');

        $this->extractor = new FrontendExposedRoutesExtractor($router, ['route_*'], $this->cacheDir);
    }

    /**
     * @dataProvider isRouteExposedProvider
     *
     * @param Route $route
     * @param string $name
     * @param bool $expected
     */
    public function testIsRouteExposed(Route $route, string $name, bool $expected): void
    {
        $this->assertEquals($expected, $this->extractor->isRouteExposed($route, $name));
    }

    /**
     * @return array
     */
    public function isRouteExposedProvider(): array
    {
        return [
            [
                'route' => new Route('route_1', [], [], ['frontend' => true, 'expose' => true]),
                'name' => 'route_1',
                'expected' => true
            ],
            [
                'route' => new Route('route_2', [], [], ['frontend' => false, 'expose' => true]),
                'name' => 'route_2',
                'expected' => false
            ],
            [
                'route' => new Route('route_3', [], [], ['expose' => true]),
                'name' => 'route_3',
                'expected' => false
            ],
            [
                'route' => new Route('route_4', [], [], ['frontend' => true, 'expose' => false]),
                'name' => 'test',
                'expected' => false
            ],
            [
                'route' => new Route('route_5', [], [], ['frontend' => true]),
                'name' => 'route_5',
                'expected' => true
            ],
            [
                'route' => new Route('route_6', [], [], ['frontend' => true, 'expose' => false]),
                'name' => 'route_6',
                'expected' => true
            ]
        ];
    }

    public function testGetCachePath(): void
    {
        $this->assertEquals($this->cacheDir . '/fosJsRouting/frontend_data.json', $this->extractor->getCachePath(''));
    }
}
