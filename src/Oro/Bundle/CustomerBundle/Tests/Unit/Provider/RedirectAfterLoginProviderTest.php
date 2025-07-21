<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Provider;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;
use Oro\Bundle\CatalogBundle\Entity\Category;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\DependencyInjection\Configuration;
use Oro\Bundle\CustomerBundle\Form\Type\RedirectAfterLoginConfigType;
use Oro\Bundle\CustomerBundle\Provider\RedirectAfterLoginProvider;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;
use Oro\Bundle\LocaleBundle\Helper\LocalizationHelper;
use Oro\Bundle\SecurityBundle\Util\SameSiteUrlHelper;
use Oro\Bundle\WebCatalogBundle\Cache\ResolvedData\ResolvedContentNode;
use Oro\Bundle\WebCatalogBundle\Cache\ResolvedData\ResolvedContentVariant;
use Oro\Bundle\WebCatalogBundle\Entity\ContentNode;
use Oro\Bundle\WebCatalogBundle\Menu\MenuContentNodesProviderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouterInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
final class RedirectAfterLoginProviderTest extends TestCase
{
    private ConfigManager&MockObject $configManager;
    private SameSiteUrlHelper&MockObject $sameSiteUrlHelper;
    private ManagerRegistry&MockObject $registry;
    private RouterInterface&MockObject $router;
    private LocalizationHelper&MockObject $localizationHelper;
    private MenuContentNodesProviderInterface&MockObject $menuContentNodesProvider;
    private RedirectAfterLoginProvider $provider;

    #[\Override]
    protected function setUp(): void
    {
        $this->configManager = $this->createMock(ConfigManager::class);
        $this->sameSiteUrlHelper = $this->createMock(SameSiteUrlHelper::class);
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->router = $this->createMock(RouterInterface::class);
        $this->localizationHelper = $this->createMock(LocalizationHelper::class);
        $this->menuContentNodesProvider = $this->createMock(MenuContentNodesProviderInterface::class);

        $this->provider = new RedirectAfterLoginProvider(
            $this->configManager,
            $this->sameSiteUrlHelper,
            $this->registry,
            $this->router,
            $this->localizationHelper,
            $this->menuContentNodesProvider
        );
    }

    public function testGetRedirectTargetTypeIsEmpty(): void
    {
        $this->configManager->expects(self::once())
            ->method('get')
            ->with(Configuration::getConfigKey(Configuration::REDIRECT_AFTER_LOGIN))
            ->willReturn([]);

        self::assertNull($this->provider->getRedirectTargetType());
    }

    public function testGetRedirectTargetType(): void
    {
        $this->configManager->expects(self::once())
            ->method('get')
            ->with(Configuration::getConfigKey(Configuration::REDIRECT_AFTER_LOGIN))
            ->willReturn(['targetType' => RedirectAfterLoginConfigType::TARGET_URI]);

        self::assertEquals(RedirectAfterLoginConfigType::TARGET_URI, $this->provider->getRedirectTargetType());
    }

    public function testGetRedirectTargetUrlIsEmpty(): void
    {
        $this->configManager->expects(self::once())
            ->method('get')
            ->with(Configuration::getConfigKey(Configuration::REDIRECT_AFTER_LOGIN))
            ->willReturn([]);

        self::assertNull($this->provider->getRedirectTargetUrl());
    }

    public function testGetRedirectTargetUrlWhenTargetTypeIsNone(): void
    {
        $url = 'https://test.com/previously_visted_page';

        $this->configManager->expects(self::once())
            ->method('get')
            ->with(Configuration::getConfigKey(Configuration::REDIRECT_AFTER_LOGIN))
            ->willReturn(['targetType' => RedirectAfterLoginConfigType::TARGET_NONE]);

        $this->sameSiteUrlHelper->expects(self::once())
            ->method('getSameSiteReferer')
            ->willReturn($url);

        self::assertEquals($url, $this->provider->getRedirectTargetUrl());
    }

    public function testGetRedirectTargetUrlWhenTargetTypeUriIsEmpty(): void
    {
        $this->configManager->expects(self::once())
            ->method('get')
            ->with(Configuration::getConfigKey(Configuration::REDIRECT_AFTER_LOGIN))
            ->willReturn([
                'targetType' => RedirectAfterLoginConfigType::TARGET_URI,
                'uri' => ''
            ]);

        $this->router->expects(self::never())
            ->method('match');

        self::assertEquals(null, $this->provider->getRedirectTargetUrl());
    }

    public function testGetRedirectTargetUrlWhenTargetTypeIsUriAndNotFoundedRoute(): void
    {
        $this->configManager->expects(self::once())
            ->method('get')
            ->with(Configuration::getConfigKey(Configuration::REDIRECT_AFTER_LOGIN))
            ->willReturn([
                'targetType' => RedirectAfterLoginConfigType::TARGET_URI,
                'uri' => 'custom_uri'
            ]);

        $this->router->expects(self::once())
            ->method('match')
            ->willReturn([]);

        self::assertEquals(null, $this->provider->getRedirectTargetUrl());
    }

    public function testGetRedirectTargetUrlWhenTargetTypeIsUri(): void
    {
        $this->configManager->expects(self::once())
            ->method('get')
            ->with(Configuration::getConfigKey(Configuration::REDIRECT_AFTER_LOGIN))
            ->willReturn([
                'targetType' => RedirectAfterLoginConfigType::TARGET_URI,
                'uri' => 'custom_uri'
            ]);

        $this->router->expects(self::once())
            ->method('match')
            ->willReturn(['_route' => 'oro_test_route']);

        self::assertEquals('/custom_uri', $this->provider->getRedirectTargetUrl());
    }

    public function testGetRedirectTargetUrlWhenTargetTypeSystemPageIsEmpty(): void
    {
        $this->configManager->expects(self::once())
            ->method('get')
            ->with(Configuration::getConfigKey(Configuration::REDIRECT_AFTER_LOGIN))
            ->willReturn([
                'targetType' => RedirectAfterLoginConfigType::TARGET_SYSTEM_PAGE,
                'systemPageRoute' => null
            ]);

        self::assertNull($this->provider->getRedirectTargetUrl());
    }

    public function testGetRedirectTargetUrlWhenTargetTypeIsSystemPage(): void
    {
        $this->configManager->expects(self::once())
            ->method('get')
            ->with(Configuration::getConfigKey(Configuration::REDIRECT_AFTER_LOGIN))
            ->willReturn([
                'targetType' => RedirectAfterLoginConfigType::TARGET_SYSTEM_PAGE,
                'systemPageRoute' => 'oro_system_page_test_route'
            ]);

        $this->router->expects(self::once())
            ->method('generate')
            ->with('oro_system_page_test_route')
            ->willReturn('/system_page_test_route');

        self::assertEquals('/system_page_test_route', $this->provider->getRedirectTargetUrl());
    }

    public function testGetRedirectTargetUrlWhenTargetTypeCategoryIsEmpty(): void
    {
        $this->configManager->expects(self::once())
            ->method('get')
            ->with(Configuration::getConfigKey(Configuration::REDIRECT_AFTER_LOGIN))
            ->willReturn([
                'targetType' => RedirectAfterLoginConfigType::TARGET_CATEGORY,
                'category' => null
            ]);

        $this->registry->expects(self::never())
            ->method('getRepository')
            ->with(Category::class);

        self::assertNull($this->provider->getRedirectTargetUrl());
    }

    public function testGetRedirectTargetUrlWhenTargetTypeNotFoundedCategory(): void
    {
        $this->configManager->expects(self::once())
            ->method('get')
            ->with(Configuration::getConfigKey(Configuration::REDIRECT_AFTER_LOGIN))
            ->willReturn([
                'targetType' => RedirectAfterLoginConfigType::TARGET_CATEGORY,
                'category' => 1
            ]);

        $objectRep = $this->createMock(ObjectRepository::class);
        $objectRep->expects(self::once())
            ->method('find')
            ->with(1)
            ->willReturn(null);

        $this->registry->expects(self::once())
            ->method('getRepository')
            ->with(Category::class)
            ->willReturn($objectRep);

        self::assertNull($this->provider->getRedirectTargetUrl());
    }

    public function testGetRedirectTargetUrlWhenTargetTypeIsCategory(): void
    {
        $category = new Category();

        $this->configManager->expects(self::once())
            ->method('get')
            ->with(Configuration::getConfigKey(Configuration::REDIRECT_AFTER_LOGIN))
            ->willReturn([
                'targetType' => RedirectAfterLoginConfigType::TARGET_CATEGORY,
                'category' => 1
            ]);

        $objectRep = $this->createMock(ObjectRepository::class);
        $objectRep->expects(self::once())
            ->method('find')
            ->with(1)
            ->willReturn($category);

        $this->registry->expects(self::once())
            ->method('getRepository')
            ->with(Category::class)
            ->willReturn($objectRep);

        $this->router->expects(self::once())
            ->method('generate')
            ->with('oro_product_frontend_product_index', ['categoryId' => 1, 'includeSubcategories' => false])
            ->willReturn('/category');

        self::assertEquals('/category', $this->provider->getRedirectTargetUrl());
    }

    public function testGetRedirectTargetUrlWhenTargetTypeNotFoundedContentNode(): void
    {
        $this->configManager->expects(self::once())
            ->method('get')
            ->with(Configuration::getConfigKey(Configuration::REDIRECT_AFTER_LOGIN))
            ->willReturn([
                'targetType' => RedirectAfterLoginConfigType::TARGET_CONTENT_NODE,
                'contentNode' => 1
            ]);

        $objectRep = $this->createMock(ObjectRepository::class);
        $objectRep->expects(self::once())
            ->method('find')
            ->with(1)
            ->willReturn(null);

        $this->registry->expects(self::once())
            ->method('getRepository')
            ->with(ContentNode::class)
            ->willReturn($objectRep);

        $this->menuContentNodesProvider->expects(self::never())
            ->method('getResolvedContentNode');

        self::assertNull($this->provider->getRedirectTargetUrl());
    }

    public function testGetRedirectTargetUrlWhenTargetTypeNotResolvedContentNode(): void
    {
        $contentNode = new ContentNode();

        $this->configManager->expects(self::once())
            ->method('get')
            ->with(Configuration::getConfigKey(Configuration::REDIRECT_AFTER_LOGIN))
            ->willReturn([
                'targetType' => RedirectAfterLoginConfigType::TARGET_CONTENT_NODE,
                'contentNode' => 1
            ]);

        $objectRep = $this->createMock(ObjectRepository::class);
        $objectRep->expects(self::once())
            ->method('find')
            ->with(1)
            ->willReturn($contentNode);

        $this->registry->expects(self::once())
            ->method('getRepository')
            ->with(ContentNode::class)
            ->willReturn($objectRep);

        $this->menuContentNodesProvider->expects(self::once())
            ->method('getResolvedContentNode')
            ->with($contentNode, ['tree_depth' => 0])
            ->willReturn(null);

        $this->localizationHelper->expects(self::never())
            ->method('getLocalizedValue');

        self::assertNull($this->provider->getRedirectTargetUrl());
    }

    public function testGetRedirectTargetUrlWhenTargetTypeIsContentNode(): void
    {
        $contentNode = new ContentNode();
        $resolvedNode = new ResolvedContentNode(1, 'test', 1, new ArrayCollection([]), new ResolvedContentVariant());

        $this->configManager->expects(self::once())
            ->method('get')
            ->with(Configuration::getConfigKey(Configuration::REDIRECT_AFTER_LOGIN))
            ->willReturn([
                'targetType' => RedirectAfterLoginConfigType::TARGET_CONTENT_NODE,
                'contentNode' => 1
            ]);

        $objectRep = $this->createMock(ObjectRepository::class);
        $objectRep->expects(self::once())
            ->method('find')
            ->with(1)
            ->willReturn($contentNode);

        $this->registry->expects(self::once())
            ->method('getRepository')
            ->with(ContentNode::class)
            ->willReturn($objectRep);

        $this->menuContentNodesProvider->expects(self::once())
            ->method('getResolvedContentNode')
            ->with($contentNode, ['tree_depth' => 0])
            ->willReturn($resolvedNode);

        $this->localizationHelper->expects(self::once())
            ->method('getLocalizedValue')
            ->with(new ArrayCollection([]))
            ->willReturn((new LocalizedFallbackValue())->setString('/content-node'));

        self::assertEquals('/content-node', $this->provider->getRedirectTargetUrl());
    }
}
