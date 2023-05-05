<?php

namespace Oro\Bundle\FrontendBundle\Api;

use Oro\Bundle\ApiBundle\Request\RequestType;

/**
 * Resolves a resource type by a route name.
 * The route name should be specified in the "routeName" DIC tag attribute.
 */
class ResourceTypeResolver implements ResourceTypeResolverInterface
{
    private string $resourceType;
    /** @var string[] */
    private array $routeParameterNames;

    /**
     * @param string   $resourceType
     * @param string[] $routeParameterNames
     */
    public function __construct(string $resourceType, array $routeParameterNames = [])
    {
        $this->resourceType = $resourceType;
        $this->routeParameterNames = $routeParameterNames;
    }

    /**
     * {@inheritDoc}
     */
    public function resolveType(string $routeName, array $routeParameters, RequestType $requestType): ?string
    {
        foreach ($this->routeParameterNames as $name) {
            if (!\array_key_exists($name, $routeParameters)) {
                return null;
            }
        }

        return $this->resourceType;
    }
}
