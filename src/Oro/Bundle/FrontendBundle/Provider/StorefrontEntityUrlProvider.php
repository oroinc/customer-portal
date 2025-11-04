<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Provider;

use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\EntityConfigBundle\Provider\AbstractEntityUrlProvider;
use Symfony\Component\Routing\RouterInterface;

/**
 * Provides entity index, view, update and create URLs on the storefront, if available.
 */
class StorefrontEntityUrlProvider extends AbstractEntityUrlProvider
{
    public function __construct(
        RouterInterface $router,
        protected readonly array $storefrontEntityRoutes
    ) {
        $this->router = $router;
    }

    public function getRoute(
        object|string $entity,
        string $routeType = self::ROUTE_INDEX,
        bool $throwExceptionIfNotDefined = false
    ): ?string {
        $entityFQCN = \is_object($entity) ? ClassUtils::getClass($entity) : $entity;

        $result = $this->storefrontEntityRoutes[$entityFQCN][$routeType] ?? null;
        if ($throwExceptionIfNotDefined && !$result) {
            throw new \LogicException(sprintf('No route "%s" found for entity "%s"', $routeType, $entityFQCN));
        }
        return $result;
    }
}
