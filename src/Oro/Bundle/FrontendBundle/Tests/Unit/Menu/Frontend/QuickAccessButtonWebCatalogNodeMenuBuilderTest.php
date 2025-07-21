<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Menu\Frontend;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;
use Knp\Menu\ItemInterface;
use Oro\Bundle\CommerceMenuBundle\Handler\ContentNodeSubFolderUriHandler;
use Oro\Bundle\FrontendBundle\Menu\Frontend\QuickAccessButtonWebCatalogNodeMenuBuilder;
use Oro\Bundle\FrontendBundle\Model\QuickAccessButtonConfig;
use Oro\Bundle\LocaleBundle\Helper\LocalizationHelper;
use Oro\Bundle\WebCatalogBundle\Cache\ResolvedData\ResolvedContentNode;
use Oro\Bundle\WebCatalogBundle\Entity\ContentNode;
use Oro\Bundle\WebCatalogBundle\Menu\MenuContentNodesProviderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class QuickAccessButtonWebCatalogNodeMenuBuilderTest extends TestCase
{
    private MenuContentNodesProviderInterface&MockObject $menuContentNodesProvider;
    private ObjectRepository&MockObject $repository;
    private QuickAccessButtonWebCatalogNodeMenuBuilder $builder;

    #[\Override]
    protected function setUp(): void
    {
        $this->menuContentNodesProvider = $this->createMock(MenuContentNodesProviderInterface::class);

        $localizationHelper = $this->createMock(LocalizationHelper::class);
        $localizationHelper->expects(self::any())
            ->method('getCurrentLocalization')
            ->willReturn(null);
        $localizationHelper->expects(self::any())
            ->method('getLocalizedValue')
            ->willReturn('localizedValue');

        $subFolderUriHandler = $this->createMock(ContentNodeSubFolderUriHandler::class);
        $subFolderUriHandler->expects(self::any())
            ->method('handle')
            ->willReturnCallback(fn ($arg) => 'uri_localizedValue');

        $this->repository = $this->createMock(ObjectRepository::class);
        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects(self::any())
            ->method('getRepository')
            ->with(ContentNode::class)
            ->willReturn($this->repository);

        $this->builder = new QuickAccessButtonWebCatalogNodeMenuBuilder(
            $this->menuContentNodesProvider,
            $localizationHelper,
            $subFolderUriHandler,
            $doctrine
        );
    }

    public function testBuild(): void
    {
        $menuItem = $this->createMock(ItemInterface::class);

        $config = (new QuickAccessButtonConfig())
            ->setType(QuickAccessButtonConfig::TYPE_WEB_CATALOG_NODE)
            ->setWebCatalogNode(1);

        $node = $this->createMock(ContentNode::class);
        $this->repository->expects(self::once())
            ->method('find')
            ->with(1)
            ->willReturn($node);

        $resolvedNode = $this->createMock(ResolvedContentNode::class);
        $this->menuContentNodesProvider->expects(self::once())
            ->method('getResolvedContentNode')
            ->with($node, [
                'tree_depth' => 0,
            ])
            ->willReturn($resolvedNode);

        $menuItem->expects(self::once())
            ->method('setUri')
            ->with('uri_localizedValue');
        $menuItem->expects(self::once())
            ->method('setExtra')
            ->with('translate_disabled', true);

        $this->builder->build($menuItem, [
            'test' => 'test',
            'qab_config' => $config,
        ], 'quick_access_button_menu');
    }

    public function testBuildWithUnsupportedAlias(): void
    {
        $menuItem = $this->createMock(ItemInterface::class);

        $this->menuContentNodesProvider->expects(self::never())
            ->method(self::anything());
        $this->repository->expects(self::never())
            ->method(self::anything());
        $menuItem->expects(self::never())
            ->method(self::anything());

        $this->builder->build($menuItem, ['test' => 'test'], 'non_supported');
    }

    public function testBuildWithUnsupportedType(): void
    {
        $menuItem = $this->createMock(ItemInterface::class);

        $config = (new QuickAccessButtonConfig())
            ->setType(QuickAccessButtonConfig::TYPE_MENU)
            ->setMenu('test_menu');

        $this->menuContentNodesProvider->expects(self::never())
            ->method(self::anything());
        $this->repository->expects(self::never())
            ->method(self::anything());
        $menuItem->expects(self::never())
            ->method(self::anything());

        $this->builder->build($menuItem, [
            'test' => 'test',
            'qab_config' => $config,
        ], 'quick_access_button_menu');
    }

    public function testBuildWithNonExistingNode(): void
    {
        $menuItem = $this->createMock(ItemInterface::class);

        $config = (new QuickAccessButtonConfig())
            ->setType(QuickAccessButtonConfig::TYPE_WEB_CATALOG_NODE)
            ->setWebCatalogNode(1);

        $this->repository->expects(self::once())
            ->method('find')
            ->with(1)
            ->willReturn(null);

        $this->menuContentNodesProvider->expects(self::never())
            ->method(self::anything());

        $menuItem->expects(self::once())
            ->method('setExtra')
            ->with('menu_not_resolved', true);

        $this->builder->build($menuItem, [
            'test' => 'test',
            'qab_config' => $config,
        ], 'quick_access_button_menu');
    }

    public function testBuildWithNonResolvedNode(): void
    {
        $menuItem = $this->createMock(ItemInterface::class);

        $config = (new QuickAccessButtonConfig())
            ->setType(QuickAccessButtonConfig::TYPE_WEB_CATALOG_NODE)
            ->setWebCatalogNode(1);

        $node = $this->createMock(ContentNode::class);
        $this->repository->expects(self::once())
            ->method('find')
            ->with(1)
            ->willReturn($node);

        $this->menuContentNodesProvider->expects(self::once())
            ->method('getResolvedContentNode')
            ->with($node, [
                'tree_depth' => 0,
            ])
            ->willReturn(null);

        $menuItem->expects(self::once())
            ->method('setExtra')
            ->with('menu_not_resolved', true);

        $this->builder->build($menuItem, [
            'test' => 'test',
            'qab_config' => $config,
        ], 'quick_access_button_menu');
    }
}
