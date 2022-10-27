<?php

namespace Oro\Bundle\FrontendAttachmentBundle\Routing;

use Oro\Component\Routing\Resolver\RouteCollectionAccessor;
use Oro\Component\Routing\Resolver\RouteOptionsResolverInterface;
use Symfony\Component\Routing\Route;

/**
 * Makes liip imagine routes as storefront ones.
 */
class ImagineRouteOptionsResolver implements RouteOptionsResolverInterface
{
    /**
     * {@inheritDoc}
     */
    public function resolve(Route $route, RouteCollectionAccessor $routes): void
    {
        if (str_starts_with($route->getPath(), '/media/cache/resolve/')) {
            $route->setOption('frontend', true);
        }
    }
}
