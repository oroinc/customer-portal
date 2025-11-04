<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Provider;

use Oro\Bundle\EntityConfigBundle\Provider\EntityUrlProviderInterface;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;

/**
 * Decorates entity URL provider to return storefront URLs for storefront requests
 * and back-office URLs for back-office requests.
 */
class EntityUrlProviderDecorator implements EntityUrlProviderInterface
{
    public function __construct(
        protected readonly EntityUrlProviderInterface $backendProvider,
        protected readonly EntityUrlProviderInterface $storefrontProvider,
        protected readonly FrontendHelper $frontendHelper,
    ) {
    }

    public function getRoute(
        object|string $entity,
        string $routeType = self::ROUTE_INDEX,
        bool $throwExceptionIfNotDefined = false
    ): ?string {
        return $this->frontendHelper->isFrontendRequest()
            ? $this->storefrontProvider->getRoute($entity, $routeType, $throwExceptionIfNotDefined)
            : $this->backendProvider->getRoute($entity, $routeType, $throwExceptionIfNotDefined);
    }

    public function getIndexUrl(object|string $entity, array $extraRouteParams = []): ?string
    {
        return $this->frontendHelper->isFrontendRequest()
            ? $this->storefrontProvider->getIndexUrl($entity, $extraRouteParams)
            : $this->backendProvider->getIndexUrl($entity, $extraRouteParams);
    }

    public function getViewUrl(object|string $entity, int $entityId, array $extraRouteParams = []): ?string
    {
        return $this->frontendHelper->isFrontendRequest()
            ? $this->storefrontProvider->getViewUrl($entity, $entityId, $extraRouteParams)
            : $this->backendProvider->getViewUrl($entity, $entityId, $extraRouteParams);
    }

    public function getUpdateUrl(object|string $entity, int $entityId, array $extraRouteParams = []): ?string
    {
        return $this->frontendHelper->isFrontendRequest()
            ? $this->storefrontProvider->getUpdateUrl($entity, $entityId, $extraRouteParams)
            : $this->backendProvider->getUpdateUrl($entity, $entityId, $extraRouteParams);
    }

    public function getCreateUrl(object|string $entity, array $extraRouteParams = []): ?string
    {
        return $this->frontendHelper->isFrontendRequest()
            ? $this->storefrontProvider->getCreateUrl($entity, $extraRouteParams)
            : $this->backendProvider->getCreateUrl($entity, $extraRouteParams);
    }
}
