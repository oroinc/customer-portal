<?php
declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Extractor;

use FOS\JsRoutingBundle\Extractor\ExposedRoutesExtractor;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

/**
 * Expose routes which marked with option `frontend: true`.
 */
class FrontendExposedRoutesExtractor extends ExposedRoutesExtractor
{
    private RouterInterface $router;

    public function __construct(
        RouterInterface $router,
        array           $routesToExpose,
        string          $cacheDir,
        array           $bundles = []
    ) {
        $this->router = $router;
        parent::__construct($router, $routesToExpose, $cacheDir, $bundles);
    }

    public function getRoutes(): RouteCollection
    {
        $this->removeNegativeExposeParameterInRoutes(...$this->router->getRouteCollection());

        $routes = parent::getRoutes();
        $storefrontRoutes = new RouteCollection();
        foreach ($routes->all() as $name => $route) {
            if ($route->hasOption('frontend') && $route->getOption('frontend')) {
                $storefrontRoutes->add($name, $route);
            }
        }
        return $storefrontRoutes;
    }

    public function isRouteExposed(Route $route, $name): bool
    {
        $this->removeNegativeExposeParameterInRoutes($route);

        return parent::isRouteExposed($route, $name) &&
            $route->hasOption('frontend') &&
            $route->getOption('frontend');
    }

    public function getCachePath(?string $locale = null): string
    {
        $path = parent::getCachePath($locale);
        $fileName = basename($path);
        $fileName = 'frontend_' . $fileName;

        return dirname($path) . DIRECTORY_SEPARATOR . $fileName;
    }

    private function removeNegativeExposeParameterInRoutes(Route ...$routes)
    {
        foreach ($routes as $route) {
            if ($route->hasOption('expose') && $route->getOption('expose') === false) {
                $route->setOption('expose', null);
            }
        }
    }
}
