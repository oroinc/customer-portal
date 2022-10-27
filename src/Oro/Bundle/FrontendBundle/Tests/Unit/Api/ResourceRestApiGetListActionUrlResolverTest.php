<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Api;

use Oro\Bundle\ApiBundle\Request\DataType;
use Oro\Bundle\ApiBundle\Request\RequestType;
use Oro\Bundle\ApiBundle\Request\Rest\RestRoutes;
use Oro\Bundle\ApiBundle\Request\Rest\RestRoutesRegistry;
use Oro\Bundle\ApiBundle\Request\ValueNormalizer;
use Oro\Bundle\FrontendBundle\Api\ResourceRestApiGetListActionUrlResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ResourceRestApiGetListActionUrlResolverTest extends \PHPUnit\Framework\TestCase
{
    public function testResolveApiUrl()
    {
        $routeName = 'test_route';
        $requestType = new RequestType(['test']);
        $entityClass = 'Test\Entity';
        $entityType = 'test_entity_type';
        $listRouteName = 'api_list_route';
        $resolvedUrl = 'test';

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $restRoutesRegistry = $this->createMock(RestRoutesRegistry::class);
        $restRoutes = $this->createMock(RestRoutes::class);
        $valueNormalizer = $this->createMock(ValueNormalizer::class);

        $valueNormalizer->expects(self::once())
            ->method('normalizeValue')
            ->with($entityClass, DataType::ENTITY_TYPE, $requestType)
            ->willReturn($entityType);
        $restRoutesRegistry->expects(self::once())
            ->method('getRoutes')
            ->with($requestType)
            ->willReturn($restRoutes);
        $restRoutes->expects(self::once())
            ->method('getListRouteName')
            ->willReturn($listRouteName);
        $urlGenerator->expects(self::once())
            ->method('generate')
            ->with($listRouteName, ['entity' => $entityType])
            ->willReturn($resolvedUrl);

        $resolver = new ResourceRestApiGetListActionUrlResolver(
            $urlGenerator,
            $restRoutesRegistry,
            $valueNormalizer,
            $entityClass
        );

        self::assertEquals(
            $resolvedUrl,
            $resolver->resolveApiUrl($routeName, [], 'test_resource', $requestType)
        );
    }
}
