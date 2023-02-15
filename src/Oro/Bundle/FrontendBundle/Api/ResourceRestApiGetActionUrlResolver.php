<?php

namespace Oro\Bundle\FrontendBundle\Api;

use Oro\Bundle\ApiBundle\Request\RequestType;
use Oro\Bundle\ApiBundle\Request\Rest\RestRoutesRegistry;
use Oro\Bundle\ApiBundle\Request\ValueNormalizer;
use Oro\Bundle\ApiBundle\Util\ValueNormalizerUtil;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Resolves the URL of the "get" action REST API resource by a route name.
 * The route name should be specified in the "routeName" DIC tag attribute.
 */
class ResourceRestApiGetActionUrlResolver implements ResourceApiUrlResolverInterface
{
    private UrlGeneratorInterface $urlGenerator;
    private RestRoutesRegistry $routesRegistry;
    private ValueNormalizer $valueNormalizer;
    private string $entityClass;
    private string $entityIdParameterName;
    private ?string $defaultEntityId = null;

    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        RestRoutesRegistry $routesRegistry,
        ValueNormalizer $valueNormalizer,
        string $entityClass,
        string $entityIdParameterName = 'id'
    ) {
        $this->urlGenerator = $urlGenerator;
        $this->routesRegistry = $routesRegistry;
        $this->valueNormalizer = $valueNormalizer;
        $this->entityClass = $entityClass;
        $this->entityIdParameterName = $entityIdParameterName;
    }

    /**
     * Sets a predefined identifier of API resource that should be used
     * if an entity identifier does not exist route parameters or it is NULL.
     * @see \Oro\Bundle\ApiBundle\Request\EntityIdResolverInterface
     */
    public function setDefaultEntityId(string $entityId): void
    {
        $this->defaultEntityId = $entityId;
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
        $entityType = ValueNormalizerUtil::convertToEntityType(
            $this->valueNormalizer,
            $this->entityClass,
            $requestType
        );

        return $this->urlGenerator->generate(
            $this->routesRegistry->getRoutes($requestType)->getItemRouteName(),
            ['entity' => $entityType, 'id' => $routeParameters[$this->entityIdParameterName] ?? $this->defaultEntityId]
        );
    }
}
