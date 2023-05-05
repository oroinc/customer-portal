<?php

namespace Oro\Bundle\FrontendBundle\EventListener;

use Oro\Bundle\DistributionBundle\Event\RouteCollectionEvent;

/**
 * Sets "frontend" option to the specified routes.
 */
class FrontendRouteCollectionListener
{
    private const OPTION_FRONTEND = 'frontend';

    /** @var string[] */
    private array $routeNames;

    /**
     * @param string[] $routeNames
     */
    public function __construct(array $routeNames)
    {
        $this->routeNames = $routeNames;
    }

    public function onCollectionAutoload(RouteCollectionEvent $event): void
    {
        if (!$this->routeNames) {
            return;
        }

        $collection = $event->getCollection();
        foreach ($this->routeNames as $routeName) {
            $collection->get($routeName)?->setOption(self::OPTION_FRONTEND, true);
        }
    }
}
