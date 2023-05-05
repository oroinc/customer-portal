<?php

namespace Oro\Bundle\FrontendBundle\Api;

use Oro\Bundle\ApiBundle\Request\RequestType;
use Oro\Bundle\ApiBundle\Util\RequestExpressionMatcher;
use Psr\Container\ContainerInterface;

/**
 * Delegates the resolving of the URL of an API resource to child resolvers.
 */
class ChainResourceApiUrlResolver implements ResourceApiUrlResolverInterface
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
    public function resolveApiUrl(
        string $routeName,
        array $routeParameters,
        string $resourceType,
        RequestType $requestType
    ): ?string {
        foreach ($this->resolvers as [$resolverServiceId, $resolverRouteName, $resolverRequestTypeExpr]) {
            if ((!$resolverRouteName || $resolverRouteName === $routeName)
                && (!$resolverRequestTypeExpr || $this->matcher->matchValue($resolverRequestTypeExpr, $requestType))
            ) {
                /** @var ResourceApiUrlResolverInterface $resolver */
                $resolver = $this->container->get($resolverServiceId);
                $url = $resolver->resolveApiUrl($routeName, $routeParameters, $resourceType, $requestType);
                if (null !== $url) {
                    return $url;
                }
            }
        }

        return null;
    }
}
