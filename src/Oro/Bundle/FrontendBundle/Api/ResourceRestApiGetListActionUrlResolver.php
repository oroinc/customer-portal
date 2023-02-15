<?php

namespace Oro\Bundle\FrontendBundle\Api;

use Oro\Bundle\ApiBundle\Request\RequestType;
use Oro\Bundle\ApiBundle\Request\Rest\RestRoutesRegistry;
use Oro\Bundle\ApiBundle\Request\ValueNormalizer;
use Oro\Bundle\ApiBundle\Util\ValueNormalizerUtil;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Resolves the URL of the "get_list" action REST API resource by a route name.
 * The route name should be specified in the "routeName" DIC tag attribute.
 */
class ResourceRestApiGetListActionUrlResolver implements ResourceApiUrlResolverInterface
{
    private UrlGeneratorInterface $urlGenerator;
    private RestRoutesRegistry $routesRegistry;
    private ValueNormalizer $valueNormalizer;
    private string $entityClass;

    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        RestRoutesRegistry $routesRegistry,
        ValueNormalizer $valueNormalizer,
        string $entityClass
    ) {
        $this->urlGenerator = $urlGenerator;
        $this->routesRegistry = $routesRegistry;
        $this->valueNormalizer = $valueNormalizer;
        $this->entityClass = $entityClass;
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
            $this->routesRegistry->getRoutes($requestType)->getListRouteName(),
            ['entity' => $entityType]
        );
    }
}
