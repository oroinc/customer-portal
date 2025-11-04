<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Twig;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityConfigBundle\Provider\EntityUrlProviderInterface;
use Oro\Bundle\FrontendBundle\Provider\StorefrontEntityUrlProvider;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\FrontendBundle\Twig\FrontendExtension;
use Oro\Bundle\UIBundle\ContentProvider\ContentProviderManager;
use Oro\Component\Testing\Unit\TwigExtensionTestCaseTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class FrontendExtensionTest extends TestCase
{
    use TwigExtensionTestCaseTrait;

    private Environment|MockObject $environment;

    private FrontendHelper|MockObject $frontendHelper;

    private RouterInterface|MockObject $router;

    private ContentProviderManager|MockObject $contentProviderManager;

    private ContentProviderManager|MockObject $frontendContentProviderManager;

    private StorefrontEntityUrlProvider|MockObject $storefrontEntityUrlProvider;

    private DoctrineHelper|MockObject $doctrineHelper;

    private FrontendExtension $extension;

    #[\Override]
    protected function setUp(): void
    {
        $this->environment = $this->createMock(Environment::class);
        $this->frontendHelper = $this->createMock(FrontendHelper::class);
        $this->router = $this->createMock(RouterInterface::class);
        $this->contentProviderManager = $this->createMock(ContentProviderManager::class);
        $this->frontendContentProviderManager = $this->createMock(ContentProviderManager::class);
        $this->storefrontEntityUrlProvider = $this->createMock(StorefrontEntityUrlProvider::class);
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);

        $container = self::getContainerBuilder()
            ->add(FrontendHelper::class, $this->frontendHelper)
            ->add(RouterInterface::class, $this->router)
            ->add('oro_ui.content_provider.manager', $this->contentProviderManager)
            ->add('oro_frontend.content_provider.manager', $this->frontendContentProviderManager)
            ->add(StorefrontEntityUrlProvider::class, $this->storefrontEntityUrlProvider)
            ->add(DoctrineHelper::class, $this->doctrineHelper)
            ->getContainer($this);

        $this->extension = new FrontendExtension($container);
    }

    /**
     * @dataProvider getDefaultPageDataProvider
     */
    public function testGetDefaultPage(bool $isFrontendRequest, string $routeName): void
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn($isFrontendRequest);

        $this->router->expects(self::once())
            ->method('generate')
            ->with($routeName)
            ->willReturn($url = 'http://sample-app/sample-url');

        self::assertEquals($url, self::callTwigFunction($this->extension, 'oro_default_page', [$this->environment]));
    }

    public function getDefaultPageDataProvider(): array
    {
        return [
            [
                'isFrontendRequest' => false,
                'routeName' => 'oro_default',
            ],
            [
                'isFrontendRequest' => true,
                'routeName' => 'oro_frontend_root',
            ],
        ];
    }

    /**
     * @dataProvider contentDataProvider
     */
    public function testGetContentWhenFrontendRequest(
        array $content,
        ?array $additionalContent,
        ?array $keys,
        array $expected
    ): void {
        $this->frontendHelper
            ->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->contentProviderManager
            ->expects(self::never())
            ->method(self::anything());

        $this->frontendContentProviderManager
            ->expects(self::once())
            ->method('getContent')
            ->with($keys)
            ->willReturn($content);

        self::assertEquals(
            $expected,
            self::callTwigFunction($this->extension, 'oro_get_content', [$additionalContent, $keys])
        );
    }

    /**
     * @dataProvider contentDataProvider
     */
    public function testGetContentWhenNotFrontendRequest(
        array $content,
        ?array $additionalContent,
        ?array $keys,
        array $expected
    ): void {
        $this->frontendHelper
            ->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(false);

        $this->frontendContentProviderManager
            ->expects(self::never())
            ->method(self::anything());

        $this->contentProviderManager
            ->expects(self::once())
            ->method('getContent')
            ->with($keys)
            ->willReturn($content);

        self::assertEquals(
            $expected,
            self::callTwigFunction($this->extension, 'oro_get_content', [$additionalContent, $keys])
        );
    }

    public function contentDataProvider(): array
    {
        return [
            'with additional content and keys' => [
                'content' => ['b' => 'c'],
                'additionalContent' => ['a' => 'b'],
                'keys' => ['a', 'b', 'c'],
                'expected' => ['a' => 'b', 'b' => 'c'],
            ],
            'without additional content and keys' => [
                'content' => ['b' => 'c'],
                'additionalContent' => null,
                'keys' => null,
                'expected' => ['b' => 'c'],
            ],
        ];
    }

    public function testGetStorefrontEntityRoute(): void
    {
        $entity = \stdClass::class;
        $routeType = EntityUrlProviderInterface::ROUTE_VIEW;
        $expectedRoute = 'oro_frontend_stdclass_view';

        $this->storefrontEntityUrlProvider->expects(self::once())
            ->method('getRoute')
            ->with($entity, $routeType)
            ->willReturn($expectedRoute);

        $result = self::callTwigFunction($this->extension, 'oro_storefront_entity_route', [$entity, $routeType]);

        self::assertEquals($expectedRoute, $result);
    }

    public function testGetStorefrontEntityRouteWithDefaultRouteType(): void
    {
        $entity = \stdClass::class;
        $expectedRoute = 'oro_frontend_stdclass_index';

        $this->storefrontEntityUrlProvider->expects(self::once())
            ->method('getRoute')
            ->with($entity, EntityUrlProviderInterface::ROUTE_INDEX)
            ->willReturn($expectedRoute);

        $result = self::callTwigFunction($this->extension, 'oro_storefront_entity_route', [$entity]);

        self::assertEquals($expectedRoute, $result);
    }

    public function testGetStorefrontEntityRouteReturnsNull(): void
    {
        $entity = \stdClass::class;
        $routeType = EntityUrlProviderInterface::ROUTE_UPDATE;

        $this->storefrontEntityUrlProvider->expects(self::once())
            ->method('getRoute')
            ->with($entity, $routeType)
            ->willReturn(null);

        $result = self::callTwigFunction($this->extension, 'oro_storefront_entity_route', [$entity, $routeType]);

        self::assertNull($result);
    }

    public function testGetStorefrontEntityIndexLink(): void
    {
        $entity = \stdClass::class;
        $extraParams = ['filter' => 'active'];
        $expectedUrl = '/frontend/stdclass';

        $this->storefrontEntityUrlProvider->expects(self::once())
            ->method('getIndexUrl')
            ->with($entity, $extraParams)
            ->willReturn($expectedUrl);

        $result = self::callTwigFunction(
            $this->extension,
            'oro_storefront_entity_index_link',
            [$entity, $extraParams]
        );

        self::assertEquals($expectedUrl, $result);
    }

    public function testGetStorefrontEntityIndexLinkWithoutExtraParams(): void
    {
        $entity = \stdClass::class;
        $expectedUrl = '/frontend/stdclass';

        $this->storefrontEntityUrlProvider->expects(self::once())
            ->method('getIndexUrl')
            ->with($entity, [])
            ->willReturn($expectedUrl);

        $result = self::callTwigFunction($this->extension, 'oro_storefront_entity_index_link', [$entity]);

        self::assertEquals($expectedUrl, $result);
    }

    public function testGetStorefrontEntityIndexLinkReturnsNull(): void
    {
        $entity = \stdClass::class;

        $this->storefrontEntityUrlProvider->expects(self::once())
            ->method('getIndexUrl')
            ->with($entity, [])
            ->willReturn(null);

        $result = self::callTwigFunction($this->extension, 'oro_storefront_entity_index_link', [$entity]);

        self::assertNull($result);
    }

    public function testGetStorefrontEntityViewLinkWithEntityObject(): void
    {
        $entity = new \stdClass();
        $entityId = 123;
        $extraParams = ['tab' => 'details'];
        $expectedUrl = '/frontend/stdclass/123';

        $this->doctrineHelper->expects(self::once())
            ->method('getSingleEntityIdentifier')
            ->with($entity)
            ->willReturn($entityId);

        $this->storefrontEntityUrlProvider->expects(self::once())
            ->method('getViewUrl')
            ->with($entity, $entityId, $extraParams)
            ->willReturn($expectedUrl);

        $result = self::callTwigFunction(
            $this->extension,
            'oro_storefront_entity_view_link',
            [$entity, null, $extraParams]
        );

        self::assertEquals($expectedUrl, $result);
    }

    public function testGetStorefrontEntityViewLinkWithEntityObjectAndNoExtraParams(): void
    {
        $entity = new \stdClass();
        $entityId = 456;
        $expectedUrl = '/frontend/stdclass/456';

        $this->doctrineHelper->expects(self::once())
            ->method('getSingleEntityIdentifier')
            ->with($entity)
            ->willReturn($entityId);

        $this->storefrontEntityUrlProvider->expects(self::once())
            ->method('getViewUrl')
            ->with($entity, $entityId, [])
            ->willReturn($expectedUrl);

        $result = self::callTwigFunction($this->extension, 'oro_storefront_entity_view_link', [$entity]);

        self::assertEquals($expectedUrl, $result);
    }

    public function testGetStorefrontEntityViewLinkWithStringEntityAndId(): void
    {
        $entity = \stdClass::class;
        $entityId = 789;
        $extraParams = ['redirect' => 'list'];
        $expectedUrl = '/frontend/stdclass/789';

        $this->doctrineHelper->expects(self::never())
            ->method('getSingleEntityIdentifier');

        $this->storefrontEntityUrlProvider->expects(self::once())
            ->method('getViewUrl')
            ->with($entity, $entityId, $extraParams)
            ->willReturn($expectedUrl);

        $result = self::callTwigFunction(
            $this->extension,
            'oro_storefront_entity_view_link',
            [$entity, $entityId, $extraParams]
        );

        self::assertEquals($expectedUrl, $result);
    }

    public function testGetStorefrontEntityViewLinkWithStringEntityAndNoId(): void
    {
        $entity = \stdClass::class;

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Entity ID must be specified, or pass an entity instance/proxy instead of a class name.'
        );

        self::callTwigFunction($this->extension, 'oro_storefront_entity_view_link', [$entity]);
    }

    public function testGetStorefrontEntityViewLinkReturnsNull(): void
    {
        $entity = new \stdClass();
        $entityId = 999;

        $this->doctrineHelper->expects(self::once())
            ->method('getSingleEntityIdentifier')
            ->with($entity)
            ->willReturn($entityId);

        $this->storefrontEntityUrlProvider->expects(self::once())
            ->method('getViewUrl')
            ->with($entity, $entityId, [])
            ->willReturn(null);

        $result = self::callTwigFunction($this->extension, 'oro_storefront_entity_view_link', [$entity]);

        self::assertNull($result);
    }
}
