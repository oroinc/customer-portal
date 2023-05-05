<?php

namespace Oro\Bundle\FrontendBundle\EventListener;

use Oro\Bundle\ApiBundle\ApiDoc\RestRouteOptionsResolver;
use Oro\Bundle\DistributionBundle\Event\RouteCollectionEvent;
use Symfony\Component\Routing\Route;

/**
 * Adds backend prefix for not frontend routes.
 * Fixes handling of backend routes with "override_path" option.
 */
class RouteCollectionListener
{
    private const OPTION_FRONTEND = 'frontend';

    private string $prefix;

    public function __construct(string  $prefix)
    {
        $this->prefix = trim(trim($prefix), '/');
    }

    public function onCollectionAutoload(RouteCollectionEvent $event): void
    {
        if ('' === $this->prefix) {
            return;
        }

        /** @var Route $route */
        foreach ($event->getCollection()->getIterator() as $route) {
            if ($route->hasOption(self::OPTION_FRONTEND) && $route->getOption(self::OPTION_FRONTEND)) {
                continue;
            }

            if (!$this->hasPrefix($route->getPath())) {
                $route->setPath($this->prefix . $route->getPath());
            }

            if ($route->hasOption(RestRouteOptionsResolver::OVERRIDE_PATH_OPTION)) {
                $overridePath = $route->getOption(RestRouteOptionsResolver::OVERRIDE_PATH_OPTION);
                if (!$this->hasPrefix($overridePath)) {
                    $route->setOption(
                        RestRouteOptionsResolver::OVERRIDE_PATH_OPTION,
                        $this->addPrefix($overridePath)
                    );
                }
            }
        }
    }

    private function hasPrefix(string $path): bool
    {
        $prefix = $this->prefix . '/';

        return
            str_starts_with($path, $prefix)
            || str_starts_with($path, '/' . $prefix);
    }

    private function addPrefix(string $path): string
    {
        if (str_starts_with($path, '/')) {
            return '/' . $this->prefix . $path;
        }

        return $this->prefix . '/' . $path;
    }
}
