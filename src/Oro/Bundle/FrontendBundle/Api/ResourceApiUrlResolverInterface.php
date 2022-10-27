<?php

namespace Oro\Bundle\FrontendBundle\Api;

use Oro\Bundle\ApiBundle\Request\RequestType;

/**
 * Represents a service that is used to resolve the URL of an API resource for a route.
 */
interface ResourceApiUrlResolverInterface
{
    /**
     * Resolves the URL of an API resource that corresponds the given route and its parameters.
     *
     * @param string      $routeName
     * @param array       $routeParameters
     * @param string      $resourceType
     * @param RequestType $requestType
     *
     * @return string|null The URL of an API resource or NULL if it cannot be resolved
     */
    public function resolveApiUrl(
        string $routeName,
        array $routeParameters,
        string $resourceType,
        RequestType $requestType
    ): ?string;
}
