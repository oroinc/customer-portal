<?php

namespace Oro\Bundle\FrontendBundle\Api;

use Oro\Bundle\ApiBundle\Request\RequestType;
use Oro\Bundle\ApiBundle\Util\RequestExpressionMatcher;
use Psr\Container\ContainerInterface;

/**
 * Delegates the resolving of a resource type to child resolvers.
 */
class ChainResourceTypeResolver implements ResourceTypeResolverInterface
{
    /** @var array [[resolver service id, route name, request type expression], ...] */
    private array $resolvers;
    private ContainerInterface $container;
    private RequestExpressionMatcher $matcher;

    /**
     * @param array                    $resolvers [[resolver service id, route name, request type expression], ...]
     * @param ContainerInterface       $container
     * @param RequestExpressionMatcher $matcher
     */
    public function __construct(array $resolvers, ContainerInterface $container, RequestExpressionMatcher $matcher)
    {
        $this->resolvers = $resolvers;
        $this->container = $container;
        $this->matcher = $matcher;
    }

    /**
     * {@inheritDoc}
     */
    public function resolveType(string $routeName, array $routeParameters, RequestType $requestType): ?string
    {
        foreach ($this->resolvers as [$resolverServiceId, $resolverRouteName, $resolverRequestTypeExpr]) {
            if ((!$resolverRouteName || $resolverRouteName === $routeName)
                && (!$resolverRequestTypeExpr || $this->matcher->matchValue($resolverRequestTypeExpr, $requestType))
            ) {
                /** @var ResourceTypeResolverInterface $resolver */
                $resolver = $this->container->get($resolverServiceId);
                $type = $resolver->resolveType($routeName, $routeParameters, $requestType);
                if (null !== $type) {
                    return $type;
                }
            }
        }

        return null;
    }
}
