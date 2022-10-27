<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Api;

use Oro\Bundle\ApiBundle\Request\DataType;
use Oro\Bundle\ApiBundle\Request\RequestType;
use Oro\Bundle\ApiBundle\Request\Rest\RestRoutes;
use Oro\Bundle\ApiBundle\Request\Rest\RestRoutesRegistry;
use Oro\Bundle\ApiBundle\Request\ValueNormalizer;
use Oro\Bundle\FrontendBundle\Api\ResourceRestApiGetActionUrlResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ResourceRestApiGetActionUrlResolverTest extends \PHPUnit\Framework\TestCase
{
    public function testResolveApiUrl()
    {
        $routeName = 'test_route';
        $requestType = new RequestType(['test']);
        $entityClass = 'Test\Entity';
        $entityType = 'test_entity_type';
        $entityId = 'test_id';
        $itemRouteName = 'api_item_route';
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
            ->method('getItemRouteName')
            ->willReturn($itemRouteName);
        $urlGenerator->expects(self::once())
            ->method('generate')
            ->with($itemRouteName, ['entity' => $entityType, 'id' => $entityId])
            ->willReturn($resolvedUrl);

        $resolver = new ResourceRestApiGetActionUrlResolver(
            $urlGenerator,
            $restRoutesRegistry,
            $valueNormalizer,
            $entityClass
        );
        self::assertEquals(
            $resolvedUrl,
            $resolver->resolveApiUrl($routeName, ['id' => $entityId], 'test_resource', $requestType)
        );
    }

    /**
     * @dataProvider resolveApiUrlForDefaultEntityIdDataProvider
     */
    public function testResolveApiUrlForDefaultEntityId(array $routeParameters)
    {
        $routeName = 'test_route';
        $requestType = new RequestType(['test']);
        $entityClass = 'Test\Entity';
        $entityType = 'test_entity_type';
        $defaultEntityId = 'default';
        $itemRouteName = 'api_item_route';
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
            ->method('getItemRouteName')
            ->willReturn($itemRouteName);
        $urlGenerator->expects(self::once())
            ->method('generate')
            ->with($itemRouteName, ['entity' => $entityType, 'id' => $defaultEntityId])
            ->willReturn($resolvedUrl);

        $resolver = new ResourceRestApiGetActionUrlResolver(
            $urlGenerator,
            $restRoutesRegistry,
            $valueNormalizer,
            $entityClass
        );
        $resolver->setDefaultEntityId($defaultEntityId);
        self::assertEquals(
            $resolvedUrl,
            $resolver->resolveApiUrl($routeName, $routeParameters, 'test_resource', $requestType)
        );
    }

    public function resolveApiUrlForDefaultEntityIdDataProvider(): array
    {
        return [
            ['routeParameters' => []],
            ['routeParameters' => ['id' => null]]
        ];
    }

    public function testResolveApiUrlWithDefaultEntityIdButEntityIdExistsInRouteParameters()
    {
        $routeName = 'test_route';
        $requestType = new RequestType(['test']);
        $entityClass = 'Test\Entity';
        $entityType = 'test_entity_type';
        $entityId = 'test_id';
        $itemRouteName = 'api_item_route';
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
            ->method('getItemRouteName')
            ->willReturn($itemRouteName);
        $urlGenerator->expects(self::once())
            ->method('generate')
            ->with($itemRouteName, ['entity' => $entityType, 'id' => $entityId])
            ->willReturn($resolvedUrl);

        $resolver = new ResourceRestApiGetActionUrlResolver(
            $urlGenerator,
            $restRoutesRegistry,
            $valueNormalizer,
            $entityClass
        );
        $resolver->setDefaultEntityId('default');
        self::assertEquals(
            $resolvedUrl,
            $resolver->resolveApiUrl($routeName, ['id' => $entityId], 'test_resource', $requestType)
        );
    }
}
