<?php

namespace Oro\Bundle\FrontendBundle\Api;

use Oro\Bundle\ApiBundle\Request\RequestType;

/**
 * Represents a service that is used to resolve a resource type for a route.
 */
interface ResourceTypeResolverInterface
{
    /**
     * Resolves a resource type by the given route and its parameters.
     *
     * @param string      $routeName
     * @param array       $routeParameters
     * @param RequestType $requestType
     *
     * @return string|null A string represents a resource type or NULL if it cannot be resolved
     */
    public function resolveType(string $routeName, array $routeParameters, RequestType $requestType): ?string;
}
