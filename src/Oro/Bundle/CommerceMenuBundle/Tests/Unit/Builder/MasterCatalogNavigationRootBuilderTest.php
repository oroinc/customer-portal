<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Builder;

use Oro\Bundle\CatalogBundle\Entity\Category;
use Oro\Bundle\CatalogBundle\Provider\MasterCatalogRootProviderInterface;
use Oro\Bundle\CommerceMenuBundle\Builder\CategoryTreeBuilder;
use Oro\Bundle\CommerceMenuBundle\Builder\MasterCatalogNavigationRootBuilder;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\CommerceMenuBundle\Provider\MenuTemplatesProvider;
use Oro\Bundle\NavigationBundle\Menu\ConfigurationBuilder;
use Oro\Bundle\NavigationBundle\Tests\Unit\MenuItemTestTrait;

class MasterCatalogNavigationRootBuilderTest extends \PHPUnit\Framework\TestCase
{
    use MenuItemTestTrait;

    private const MENU_TEMPLATE = 'template1';

    private MasterCatalogRootProviderInterface|\PHPUnit\Framework\MockObject\MockObject $masterCatalogRootProvider;

    private MasterCatalogNavigationRootBuilder $builder;

    protected function setUp(): void
    {
        $this->masterCatalogRootProvider = $this->createMock(MasterCatalogRootProviderInterface::class);
        $menuTemplatesProvider = $this->createMock(MenuTemplatesProvider::class);

        $this->builder = new MasterCatalogNavigationRootBuilder(
            $this->masterCatalogRootProvider,
            $menuTemplatesProvider
        );

        $menuTemplatesProvider
            ->expects(self::any())
            ->method('getMenuTemplates')
            ->willReturn([
                self::MENU_TEMPLATE => ['label' => 'Template 1'],
                'template2' => ['label' => 'Template 2'],
            ]);
    }

    public function testBuildWhenNotDisplayed(): void
    {
        $this->masterCatalogRootProvider
            ->expects(self::never())
            ->method(self::anything());

        $menu = $this->createItem('sample_menu');
        $menu->setDisplay(false);
        $this->builder->build($menu);

        self::assertNull($menu->getExtra(MenuUpdate::TARGET_CATEGORY));
    }

    public function testBuildItemWhenHasRootCategoryAndNoMaxTraverseLevel(): void
    {
        $rootCategory = new Category();
        $this->masterCatalogRootProvider->expects(self::once())
            ->method('getMasterCatalogRoot')
            ->willReturn($rootCategory);

        $menu = $this->createItem('sample_menu');
        $menu->setExtra(ConfigurationBuilder::MAX_NESTING_LEVEL, 6);
        $this->builder->build($menu);

        self::assertSame($rootCategory, $menu->getExtra(MenuUpdate::TARGET_CATEGORY));

        self::assertEquals(6, $menu->getExtra(MenuUpdate::MAX_TRAVERSE_LEVEL));
        self::assertEquals(
            ['extras' => [MenuUpdate::MENU_TEMPLATE => self::MENU_TEMPLATE]],
            $menu->getExtra(CategoryTreeBuilder::TREE_ITEM_OPTIONS)
        );
    }

    public function testBuildItemWhenHasRootCategoryAndMaxTraverseLevel(): void
    {
        $rootCategory = new Category();
        $this->masterCatalogRootProvider->expects(self::once())
            ->method('getMasterCatalogRoot')
            ->willReturn($rootCategory);

        $menu = $this->createItem('sample_menu');
        $menu->setExtra(MenuUpdate::MAX_TRAVERSE_LEVEL, 3);
        $this->builder->build($menu);

        self::assertSame($rootCategory, $menu->getExtra(MenuUpdate::TARGET_CATEGORY));

        self::assertEquals(3, $menu->getExtra(MenuUpdate::MAX_TRAVERSE_LEVEL));
        self::assertEquals(
            ['extras' => [MenuUpdate::MENU_TEMPLATE => self::MENU_TEMPLATE]],
            $menu->getExtra(CategoryTreeBuilder::TREE_ITEM_OPTIONS)
        );
    }

    public function testBuildItemWhenHasRootCategoryAndTreeItemOptions(): void
    {
        $rootCategory = new Category();
        $this->masterCatalogRootProvider->expects(self::once())
            ->method('getMasterCatalogRoot')
            ->willReturn($rootCategory);

        $menu = $this->createItem('sample_menu');
        $menu->setExtra(MenuUpdate::MAX_TRAVERSE_LEVEL, 3);
        $treeItemOptions = ['sample_key' => 'sample_value'];
        $this->builder->setTreeItemOptions($treeItemOptions);
        $this->builder->build($menu);

        self::assertSame($rootCategory, $menu->getExtra(MenuUpdate::TARGET_CATEGORY));

        self::assertEquals(3, $menu->getExtra(MenuUpdate::MAX_TRAVERSE_LEVEL));
        self::assertEquals(
            $treeItemOptions + ['extras' => [MenuUpdate::MENU_TEMPLATE => self::MENU_TEMPLATE]],
            $menu->getExtra(CategoryTreeBuilder::TREE_ITEM_OPTIONS)
        );
    }
}
