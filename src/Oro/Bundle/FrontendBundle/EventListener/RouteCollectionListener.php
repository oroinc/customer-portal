<?php

namespace Oro\Bundle\FrontendBundle\EventListener;

use Oro\Bundle\ApiBundle\ApiDoc\RestRouteOptionsResolver;
use Oro\Bundle\DistributionBundle\Event\RouteCollectionEvent;
use Symfony\Component\Routing\Route;

class RouteCollectionListener
{
    const OPTION_FRONTEND = 'frontend';

    /**
     * @var string
     */
    protected $prefix;

    /**
     * @param string $prefix
     */
    public function __construct($prefix)
    {
        $this->prefix = trim(trim($prefix), '/');
    }

    public function onCollectionAutoload(RouteCollectionEvent $event)
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

    /**
     * @param string $path
     *
     * @return bool
     */
    private function hasPrefix($path)
    {
        $prefix = $this->prefix . '/';

        return
            0 === strpos($path, $prefix)
            || 0 === strpos($path, '/' . $prefix);
    }

    /**
     * @param string $path
     *
     * @return string
     */
    private function addPrefix($path)
    {
        if (0 === strpos($path, '/')) {
            return '/' . $this->prefix . $path;
        }

        return $this->prefix . '/' . $path;
    }
}
