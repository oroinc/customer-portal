<?php
declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Extractor;

use FOS\JsRoutingBundle\Extractor\ExposedRoutesExtractor;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Expose routes which marked with option `frontend: true`.
 */
class FrontendExposedRoutesExtractor extends ExposedRoutesExtractor
{
    public function getRoutes(): RouteCollection
    {
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
        return parent::isRouteExposed($route, $name) &&
            $route->hasOption('frontend') &&
            $route->getOption('frontend');
    }

    public function getCachePath($locale): string
    {
        $path = parent::getCachePath($locale);
        $fileName = basename($path);
        $fileName = 'frontend_'.$fileName;

        return dirname($path) . DIRECTORY_SEPARATOR . $fileName;
    }
}
