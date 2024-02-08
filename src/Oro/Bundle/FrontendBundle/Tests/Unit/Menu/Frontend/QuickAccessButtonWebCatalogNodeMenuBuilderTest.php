<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Menu\Frontend;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;
use Knp\Menu\ItemInterface;
use Oro\Bundle\CommerceMenuBundle\Handler\SubFolderUriHandler;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\ConfigBundle\Tests\Functional\Traits\ConfigManagerAwareTestTrait;
use Oro\Bundle\FrontendBundle\Menu\Frontend\QuickAccessButtonWebCatalogNodeMenuBuilder;
use Oro\Bundle\FrontendBundle\Model\QuickAccessButtonConfig;
use Oro\Bundle\LocaleBundle\Helper\LocalizationHelper;
use Oro\Bundle\WebCatalogBundle\Cache\ResolvedData\ResolvedContentNode;
use Oro\Bundle\WebCatalogBundle\Cache\ResolvedData\ResolvedContentVariant;
use Oro\Bundle\WebCatalogBundle\Entity\ContentNode;
use Oro\Bundle\WebCatalogBundle\Menu\MenuContentNodesProviderInterface;
use PHPUnit\Framework\MockObject\MockObject;

class QuickAccessButtonWebCatalogNodeMenuBuilderTest extends \PHPUnit\Framework\TestCase
{
    use ConfigManagerAwareTestTrait;

    private ConfigManager|MockObject $configManager;
    private MenuContentNodesProviderInterface|MockObject $menuContentNodesProvider;
    private ObjectRepository|MockObject $repository;
    private QuickAccessButtonWebCatalogNodeMenuBuilder $builder;

    protected function setUp(): void
    {
        $this->configManager = $this->createMock(ConfigManager::class);
        $this->menuContentNodesProvider = $this->createMock(MenuContentNodesProviderInterface::class);

        $localizationHelper = $this->createMock(LocalizationHelper::class);
        $localizationHelper->expects(self::any())->method('getCurrentLocalization')->willReturn(null);
        $localizationHelper->expects(self::any())->method('getLocalizedValue')->willReturn('localizedValue');

        $subFolderUriHandler = $this->createMock(SubFolderUriHandler::class);
        $subFolderUriHandler->expects(self::any())->method('hasSubFolder')->willReturn(true);
        $subFolderUriHandler->expects(self::any())->method('handle')->willReturnCallback(fn ($arg) => 'uri_' . $arg);

        $this->repository = $this->createMock(ObjectRepository::class);
        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine
            ->expects(self::any())
            ->method('getRepository')
            ->with(ContentNode::class)
            ->willReturn($this->repository);

        $this->builder = new QuickAccessButtonWebCatalogNodeMenuBuilder(
            $this->configManager,
            $this->menuContentNodesProvider,
            $localizationHelper,
            $subFolderUriHandler,
            $doctrine
        );
    }

    public function testBuild(): void
    {
        $menuItem = $this->createMock(ItemInterface::class);

        $this->configManager
            ->expects(self::once())
            ->method('get')
            ->with('oro_frontend.quick_access_button')
            ->willReturn(
                (new QuickAccessButtonConfig())
                    ->setType(QuickAccessButtonConfig::TYPE_WEB_CATALOG_NODE)
                    ->setWebCatalogNode(1)
            );

        $node = $this->createMock(ContentNode::class);
        $this->repository
            ->expects(self::once())
            ->method('find')
            ->with(1)
            ->willReturn($node);

        $resolvedNode = $this->createMock(ResolvedContentNode::class);
        $resolvedNode
            ->expects(self::once())
            ->method('getTitles')
            ->willReturn(new ArrayCollection());
        $resolvedNode
            ->expects(self::once())
            ->method('getResolvedContentVariant')
            ->willReturn(new ResolvedContentVariant());

        $this->menuContentNodesProvider
            ->expects(self::once())
            ->method('getResolvedContentNode')
            ->with($node, [
                'tree_depth' => 0,
            ])
            ->willReturn($resolvedNode);

        $menuItem->expects(self::once())->method('setLabel')->with('localizedValue');
        $menuItem->expects(self::once())->method('setUri')->with('uri_localizedValue');
        $menuItem->expects(self::once())->method('setExtra')->with('translate_disabled', true);

        $this->builder->build($menuItem, ['test' => 'test'], 'quick_access_button_menu');
    }

    public function testBuildWithUnsupportedAlias(): void
    {
        $menuItem = $this->createMock(ItemInterface::class);

        $this->configManager->expects(self::never())->method(self::anything());
        $this->menuContentNodesProvider->expects(self::never())->method(self::anything());
        $this->repository->expects(self::never())->method(self::anything());
        $menuItem->expects(self::never())->method(self::anything());

        $this->builder->build($menuItem, ['test' => 'test'], 'non_supported');
    }

    public function testBuildWithUnsupportedType(): void
    {
        $menuItem = $this->createMock(ItemInterface::class);

        $this->configManager
            ->expects(self::once())
            ->method('get')
            ->with('oro_frontend.quick_access_button')
            ->willReturn(
                (new QuickAccessButtonConfig())
                    ->setType(QuickAccessButtonConfig::TYPE_MENU)
                    ->setMenu('test_menu')
            );
        $this->menuContentNodesProvider->expects(self::never())->method(self::anything());
        $this->repository->expects(self::never())->method(self::anything());
        $menuItem->expects(self::never())->method(self::anything());

        $this->builder->build($menuItem, ['test' => 'test'], 'quick_access_button_menu');
    }

    public function testBuildWithNonExistingNode(): void
    {
        $menuItem = $this->createMock(ItemInterface::class);

        $this->configManager
            ->expects(self::once())
            ->method('get')
            ->with('oro_frontend.quick_access_button')
            ->willReturn(
                (new QuickAccessButtonConfig())
                    ->setType(QuickAccessButtonConfig::TYPE_WEB_CATALOG_NODE)
                    ->setWebCatalogNode(1)
            );

        $this->repository
            ->expects(self::once())
            ->method('find')
            ->with(1)
            ->willReturn(null);


        $this->menuContentNodesProvider->expects(self::never())->method(self::anything());

        $menuItem->expects(self::once())->method('setExtra')->with('menu_not_resolved', true);

        $this->builder->build($menuItem, ['test' => 'test'], 'quick_access_button_menu');
    }

    public function testBuildWithNonResolvedNode(): void
    {
        $menuItem = $this->createMock(ItemInterface::class);

        $this->configManager
            ->expects(self::once())
            ->method('get')
            ->with('oro_frontend.quick_access_button')
            ->willReturn(
                (new QuickAccessButtonConfig())
                    ->setType(QuickAccessButtonConfig::TYPE_WEB_CATALOG_NODE)
                    ->setWebCatalogNode(1)
            );

        $node = $this->createMock(ContentNode::class);
        $this->repository
            ->expects(self::once())
            ->method('find')
            ->with(1)
            ->willReturn($node);

        $this->menuContentNodesProvider
            ->expects(self::once())
            ->method('getResolvedContentNode')
            ->with($node, [
                'tree_depth' => 0,
            ])
            ->willReturn(null);

        $menuItem->expects(self::once())->method('setExtra')->with('menu_not_resolved', true);

        $this->builder->build($menuItem, ['test' => 'test'], 'quick_access_button_menu');
    }
}
