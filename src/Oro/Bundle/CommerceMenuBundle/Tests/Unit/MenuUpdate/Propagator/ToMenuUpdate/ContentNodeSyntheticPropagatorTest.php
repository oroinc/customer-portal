<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\MenuUpdate\Propagator\ToMenuUpdate;

use Knp\Menu\ItemInterface;
use Oro\Bundle\CommerceMenuBundle\Builder\ContentNodeTreeBuilder;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\CommerceMenuBundle\MenuUpdate\Propagator\ToMenuUpdate\ContentNodeSyntheticPropagator;
use Oro\Bundle\NavigationBundle\Entity\MenuUpdateInterface;
use Oro\Bundle\NavigationBundle\MenuUpdate\Propagator\ToMenuUpdate\MenuItemToMenuUpdatePropagatorInterface;
use Oro\Bundle\NavigationBundle\Tests\Unit\MenuItemTestTrait;
use Oro\Bundle\WebCatalogBundle\Tests\Unit\Stub\ContentNodeStub;

class ContentNodeSyntheticPropagatorTest extends \PHPUnit\Framework\TestCase
{
    use MenuItemTestTrait;

    private ContentNodeSyntheticPropagator $propagator;

    protected function setUp(): void
    {
        $this->propagator = new ContentNodeSyntheticPropagator();
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
        $contentNode = new ContentNodeStub(42);
        $parentContentNode = new ContentNodeStub(4242);
        $anotherParentContentNode = new ContentNodeStub(424242);

        $parentMenuItemWithoutContentNode = $this->createItem('parent_menu_item');
        $parentMenuItemWithContentNode = $this->createItem('parent_menu_item_with_content_node')
            ->setExtra(MenuUpdate::TARGET_CONTENT_NODE, $parentContentNode);
        $parentMenuItemWithAnotherContentNode = $this->createItem('parent_menu_item_with_another_content_node')
            ->setExtra(MenuUpdate::TARGET_CONTENT_NODE, $anotherParentContentNode);

        $treeItemNamePrefix = ContentNodeTreeBuilder::getTreeItemNamePrefix(
            $parentMenuItemWithContentNode,
            $parentContentNode->getId()
        );
        $treeItemName = $treeItemNamePrefix . $contentNode->getId();

        $menuUpdate = (new MenuUpdate())
            ->setKey($treeItemName);

        return [
            'nothing changes when not synthetic, not tree item' => [
                'menuUpdate' => (clone $menuUpdate),
                'menuItem' => $this->createItem($menuUpdate->getKey()),
                'expected' => clone $menuUpdate,
            ],
            'nothing changes when not synthetic, is tree item, no content node' => [
                'menuUpdate' => (clone $menuUpdate),
                'menuItem' => $this->createItem($menuUpdate->getKey())
                    ->setExtra(ContentNodeTreeBuilder::IS_TREE_ITEM, true),
                'expected' => (clone $menuUpdate),
            ],
            'nothing changes when synthetic, not tree item, no content node' => [
                'menuUpdate' => (clone $menuUpdate),
                'menuItem' => $this->createItem($menuUpdate->getKey())
                    ->setExtra(MenuUpdateInterface::IS_SYNTHETIC, true),
                'expected' => (clone $menuUpdate),
            ],
            'becomes synthetic when is tree item, no menu parent' => [
                'menuUpdate' => (clone $menuUpdate),
                'menuItem' => $this->createItem($menuUpdate->getKey())
                    ->setExtra(ContentNodeTreeBuilder::IS_TREE_ITEM, true)
                    ->setExtra(MenuUpdate::TARGET_CONTENT_NODE, $contentNode),
                'expected' => (clone $menuUpdate)
                    ->setSynthetic(true),
            ],
            'becomes synthetic when is tree item, no parent menu content node' => [
                'menuUpdate' => (clone $menuUpdate),
                'menuItem' => (clone $parentMenuItemWithoutContentNode)
                    ->addChild($menuUpdate->getKey())
                    ->setExtra(ContentNodeTreeBuilder::IS_TREE_ITEM, true)
                    ->setExtra(MenuUpdate::TARGET_CONTENT_NODE, $contentNode),
                'expected' => (clone $menuUpdate)
                    ->setSynthetic(true),
            ],
            'becomes synthetic when is tree item, tree item prefix is changed' => [
                'menuUpdate' => (clone $menuUpdate),
                'menuItem' => (clone $parentMenuItemWithAnotherContentNode)
                    ->addChild($menuUpdate->getKey())
                    ->setExtra(ContentNodeTreeBuilder::IS_TREE_ITEM, true)
                    ->setExtra(MenuUpdate::TARGET_CONTENT_NODE, $contentNode),
                'expected' => (clone $menuUpdate)
                    ->setSynthetic(true),
            ],
            'nothing changes when is tree item, tree item prefix is not changed, parent content nodes equal' => [
                'menuUpdate' => (clone $menuUpdate),
                'menuItem' => (clone $parentMenuItemWithContentNode)
                    ->addChild($menuUpdate->getKey())
                    ->setExtra(ContentNodeTreeBuilder::IS_TREE_ITEM, true)
                    ->setExtra(
                        MenuUpdate::TARGET_CONTENT_NODE,
                        (clone $contentNode)->setParentNode($parentContentNode)
                    ),
                'expected' => (clone $menuUpdate),
            ],
            'becomes synthetic when is tree item, tree item prefix is not changed, parent content node changed' => [
                'menuUpdate' => (clone $menuUpdate),
                'menuItem' => (clone $parentMenuItemWithContentNode)
                    ->addChild($menuUpdate->getKey())
                    ->setExtra(ContentNodeTreeBuilder::IS_TREE_ITEM, true)
                    ->setExtra(
                        MenuUpdate::TARGET_CONTENT_NODE,
                        (clone $contentNode)->setParentNode($anotherParentContentNode)
                    ),
                'expected' => (clone $menuUpdate)
                    ->setSynthetic(true),
            ],
            'becomes not synthetic when is synthetic, tree item prefix is not changed, parent content nodes equal' => [
                'menuUpdate' => (clone $menuUpdate)
                    ->setSynthetic(true),
                'menuItem' => (clone $parentMenuItemWithContentNode)
                    ->addChild($menuUpdate->getKey())
                    ->setExtra(MenuUpdateInterface::IS_SYNTHETIC, true)
                    ->setExtra(
                        MenuUpdate::TARGET_CONTENT_NODE,
                        (clone $contentNode)->setParentNode($parentContentNode)
                    ),
                'expected' => (clone $menuUpdate)
                    ->setSynthetic(false),
            ],
        ];
    }
}
