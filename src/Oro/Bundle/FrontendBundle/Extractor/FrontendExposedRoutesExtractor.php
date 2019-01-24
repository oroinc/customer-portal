<?php

namespace Oro\Bundle\FrontendBundle\Extractor;

use FOS\JsRoutingBundle\Extractor\ExposedRoutesExtractor;
use Symfony\Component\Routing\Route;

/**
 * Expose routes which marked with option `frontend: true`.
 */
class FrontendExposedRoutesExtractor extends ExposedRoutesExtractor
{
    /**
     * {@inheritdoc}
     */
    public function isRouteExposed(Route $route, $name)
    {
        return parent::isRouteExposed($route, $name) &&
            $route->hasOption('frontend') &&
            $route->getOption('frontend');
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
