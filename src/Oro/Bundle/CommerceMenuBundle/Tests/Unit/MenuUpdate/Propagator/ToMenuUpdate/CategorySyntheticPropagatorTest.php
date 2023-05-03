<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\MenuUpdate\Propagator\ToMenuUpdate;

use Knp\Menu\ItemInterface;
use Oro\Bundle\CatalogBundle\Tests\Unit\Stub\CategoryStub;
use Oro\Bundle\CommerceMenuBundle\Builder\CategoryTreeBuilder;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\CommerceMenuBundle\MenuUpdate\Propagator\ToMenuUpdate\CategorySyntheticPropagator;
use Oro\Bundle\NavigationBundle\Entity\MenuUpdateInterface;
use Oro\Bundle\NavigationBundle\MenuUpdate\Propagator\ToMenuUpdate\MenuItemToMenuUpdatePropagatorInterface;
use Oro\Bundle\NavigationBundle\Tests\Unit\MenuItemTestTrait;

class CategorySyntheticPropagatorTest extends \PHPUnit\Framework\TestCase
{
    use MenuItemTestTrait;

    private CategorySyntheticPropagator $propagator;

    protected function setUp(): void
    {
        $this->propagator = new CategorySyntheticPropagator();
    }

    /**
     * @dataProvider isApplicableDataProvider
     */
    public function testIsApplicable(string $strategy, bool $expected): void
    {
        $menuUpdate = new MenuUpdate();
        $menuItem = $this->createMock(ItemInterface::class);

        self::assertSame(
            $expected,
            $this->propagator->isApplicable($menuUpdate, $menuItem, $strategy)
        );
    }

    /**
     * @dataProvider isApplicableDataProvider
     */
    public function testIsApplicableWhenNotMenuUpdate(string $strategy): void
    {
        $menuUpdate = $this->createMock(MenuUpdateInterface::class);
        $menuItem = $this->createMock(ItemInterface::class);

        self::assertFalse(
            $this->propagator->isApplicable($menuUpdate, $menuItem, $strategy)
        );
    }

    public function isApplicableDataProvider(): array
    {
        return [
            'none' => [
                'strategy' => MenuItemToMenuUpdatePropagatorInterface::STRATEGY_NONE,
                'expected' => false,
            ],
            'basic' => [
                'strategy' => MenuItemToMenuUpdatePropagatorInterface::STRATEGY_BASIC,
                'expected' => true,
            ],
            'full' => [
                'strategy' => MenuItemToMenuUpdatePropagatorInterface::STRATEGY_FULL,
                'expected' => true,
            ],
        ];
    }

    public function testPropagateFromMenuItemWhenNotMenuUpdate(): void
    {
        $menuUpdate = $this->createMock(MenuUpdateInterface::class);

        $menuUpdate
            ->expects(self::never())
            ->method(self::anything());

        $this->propagator->propagateFromMenuItem(
            $menuUpdate,
            $this->createMock(ItemInterface::class),
            MenuItemToMenuUpdatePropagatorInterface::STRATEGY_FULL
        );
    }

    /**
     * @dataProvider propagateFromMenuItemDataProvider
     */
    public function testPropagateFromMenuItem(
        MenuUpdate $menuUpdate,
        ItemInterface $menuItem,
        MenuUpdate $expected
    ): void {
        $this->propagator->propagateFromMenuItem(
            $menuUpdate,
            $menuItem,
            MenuItemToMenuUpdatePropagatorInterface::STRATEGY_FULL
        );

        self::assertEquals($expected, $menuUpdate);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function propagateFromMenuItemDataProvider(): array
    {
        $category = new CategoryStub(42);
        $parentCategory = new CategoryStub(4242);
        $anotherParentCategory = new CategoryStub(424242);

        $parentMenuItemWithoutCategory = $this->createItem('parent_menu_item');
        $parentMenuItemWithCategory = $this->createItem('parent_menu_item_with_category')
            ->setExtra(MenuUpdate::TARGET_CATEGORY, $parentCategory);
        $parentMenuItemWithAnotherCategory = $this->createItem('parent_menu_item_with_another_category')
            ->setExtra(MenuUpdate::TARGET_CATEGORY, $anotherParentCategory);

        $treeItemNamePrefix = CategoryTreeBuilder::getTreeItemNamePrefix(
            $parentMenuItemWithCategory,
            $parentCategory->getId()
        );
        $treeItemName = $treeItemNamePrefix . $category->getId();

        $menuUpdate = (new MenuUpdate())
            ->setKey($treeItemName);

        return [
            'nothing changes when not synthetic, not tree item' => [
                'menuUpdate' => (clone $menuUpdate),
                'menuItem' => $this->createItem($menuUpdate->getKey()),
                'expected' => clone $menuUpdate,
            ],
            'nothing changes when not synthetic, is tree item, no category' => [
                'menuUpdate' => (clone $menuUpdate),
                'menuItem' => $this->createItem($menuUpdate->getKey())
                    ->setExtra(CategoryTreeBuilder::IS_TREE_ITEM, true),
                'expected' => (clone $menuUpdate),
            ],
            'nothing changes when synthetic, not tree item, no category' => [
                'menuUpdate' => (clone $menuUpdate),
                'menuItem' => $this->createItem($menuUpdate->getKey())
                    ->setExtra(MenuUpdateInterface::IS_SYNTHETIC, true),
                'expected' => (clone $menuUpdate),
            ],
            'becomes synthetic when is tree item, no menu parent' => [
                'menuUpdate' => (clone $menuUpdate),
                'menuItem' => $this->createItem($menuUpdate->getKey())
                    ->setExtra(CategoryTreeBuilder::IS_TREE_ITEM, true)
                    ->setExtra(MenuUpdate::TARGET_CATEGORY, $category),
                'expected' => (clone $menuUpdate)
                    ->setSynthetic(true),
            ],
            'becomes synthetic when is tree item, no parent menu category' => [
                'menuUpdate' => (clone $menuUpdate),
                'menuItem' => (clone $parentMenuItemWithoutCategory)
                    ->addChild($menuUpdate->getKey())
                    ->setExtra(CategoryTreeBuilder::IS_TREE_ITEM, true)
                    ->setExtra(MenuUpdate::TARGET_CATEGORY, $category),
                'expected' => (clone $menuUpdate)
                    ->setSynthetic(true),
            ],
            'becomes synthetic when is tree item, tree item prefix is changed' => [
                'menuUpdate' => (clone $menuUpdate),
                'menuItem' => (clone $parentMenuItemWithAnotherCategory)
                    ->addChild($menuUpdate->getKey())
                    ->setExtra(CategoryTreeBuilder::IS_TREE_ITEM, true)
                    ->setExtra(MenuUpdate::TARGET_CATEGORY, $category),
                'expected' => (clone $menuUpdate)
                    ->setSynthetic(true),
            ],
            'nothing changes when is tree item, tree item prefix is not changed, parent categories equal' => [
                'menuUpdate' => (clone $menuUpdate),
                'menuItem' => (clone $parentMenuItemWithCategory)
                    ->addChild($menuUpdate->getKey())
                    ->setExtra(CategoryTreeBuilder::IS_TREE_ITEM, true)
                    ->setExtra(
                        MenuUpdate::TARGET_CATEGORY,
                        (clone $category)->setParentCategory($parentCategory)
                    ),
                'expected' => (clone $menuUpdate),
            ],
            'becomes synthetic when is tree item, tree item prefix is not changed, parent category changed' => [
                'menuUpdate' => (clone $menuUpdate),
                'menuItem' => (clone $parentMenuItemWithCategory)
                    ->addChild($menuUpdate->getKey())
                    ->setExtra(CategoryTreeBuilder::IS_TREE_ITEM, true)
                    ->setExtra(
                        MenuUpdate::TARGET_CATEGORY,
                        (clone $category)->setParentCategory($anotherParentCategory)
                    ),
                'expected' => (clone $menuUpdate)
                    ->setSynthetic(true),
            ],
            'becomes not synthetic when is synthetic, tree item prefix is not changed, parent categories equal' => [
                'menuUpdate' => (clone $menuUpdate)
                    ->setSynthetic(true),
                'menuItem' => (clone $parentMenuItemWithCategory)
                    ->addChild($menuUpdate->getKey())
                    ->setExtra(MenuUpdateInterface::IS_SYNTHETIC, true)
                    ->setExtra(
                        MenuUpdate::TARGET_CATEGORY,
                        (clone $category)->setParentCategory($parentCategory)
                    ),
                'expected' => (clone $menuUpdate)
                    ->setSynthetic(false),
            ],
        ];
    }
}
