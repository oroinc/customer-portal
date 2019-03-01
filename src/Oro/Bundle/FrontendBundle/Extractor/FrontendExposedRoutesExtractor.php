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
        $pattern = $this->buildPattern();

        return true === $route->getOption('frontend')
            || 'true' === $route->getOption('frontend')
            || ('' !== $pattern && preg_match('#' . $pattern . '#', $name));
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
