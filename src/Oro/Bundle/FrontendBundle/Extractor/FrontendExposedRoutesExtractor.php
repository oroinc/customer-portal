<?php

namespace Oro\Bundle\FrontendBundle\Extractor;

use FOS\JsRoutingBundle\Extractor\ExposedRoutesExtractor;
use Symfony\Component\Routing\Route;

class FrontendExposedRoutesExtractor extends ExposedRoutesExtractor
{
    /**
     * {@inheritdoc}
     */
    public function getExposedRoutes()
    {
        $routes = parent::getExposedRoutes();
        $routes = array_filter($routes, function (Route $route) {
            return $route->hasOption('frontend') && $route->getOption('frontend');
        }, ARRAY_FILTER_USE_BOTH);

        return $routes;
    }

    /**
     * {@inheritdoc}
     */
    public function getCachePath($locale)
    {
        $path = parent::getCachePath($locale);
        $fileName = basename($path);
        $fileName = 'frontend_'.$fileName;

        return dirname($path) . DIRECTORY_SEPARATOR . $fileName;
    }
}
