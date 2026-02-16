<?php

namespace Oro\Bundle\CommerceMenuBundle\Api;

use Oro\Bundle\ApiBundle\Request\RequestType;
use Oro\Bundle\FrontendBundle\Api\ResourceApiUrlResolverInterface;
use Oro\Bundle\FrontendBundle\Api\ResourceTypeResolverInterface;
use Oro\Bundle\RedirectBundle\Api\Repository\RouteRepository;

/**
 * Provides route information for multiple URIs in batch.
 */
class RouteInfoProvider
{
    private const string UNKNOWN_RESOURCE_TYPE = 'unknown';

    public function __construct(
        private readonly RouteRepository $routeRepository,
        private readonly ResourceTypeResolverInterface $resourceTypeResolver,
        private readonly ResourceApiUrlResolverInterface $apiUrlResolver
    ) {
    }

    /**
     * Gets route information for multiple URIs.
     *
     * @param array<string> $uris List of URIs to process
     * @param RequestType $requestType
     * @return array<string, array|null> Array with URI as key and route info as value (null if route not found)
     */
    public function getRoutesInfo(array $uris, RequestType $requestType): array
    {
        $result = [];
        $uniqueUris = array_unique(array_filter($uris));

        foreach ($uniqueUris as $uri) {
            if (!str_starts_with($uri, '/')) {
                $result[$uri] = null;
                continue;
            }

            $routeId = str_replace('/', ':', $uri);
            $route = $this->routeRepository->findRoute($routeId);
            if ($route === null) {
                $result[$uri] = null;
                continue;
            }

            $resourceType = $this->resourceTypeResolver->resolveType(
                $route->getRouteName(),
                $route->getRouteParameters(),
                $requestType
            );
            if ($resourceType === null) {
                $resourceType = self::UNKNOWN_RESOURCE_TYPE;
            }

            $apiUrl = null;
            if ($resourceType !== self::UNKNOWN_RESOURCE_TYPE) {
                $apiUrl = $this->apiUrlResolver->resolveApiUrl(
                    $route->getRouteName(),
                    $route->getRouteParameters(),
                    $resourceType,
                    $requestType
                );
            }

            $result[$uri] = [
                'isSlug' => $route->isSlug(),
                'redirectUrl' => $route->getRedirectUrl(),
                'redirectStatusCode' => $route->getRedirectStatusCode(),
                'resourceType' => $resourceType,
                'apiUrl' => $apiUrl,
            ];
        }

        return $result;
    }
}
