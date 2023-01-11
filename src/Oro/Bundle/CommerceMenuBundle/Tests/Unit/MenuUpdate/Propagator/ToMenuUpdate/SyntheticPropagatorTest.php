<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\MenuUpdate\Propagator\ToMenuUpdate;

use Knp\Menu\ItemInterface;
use Oro\Bundle\CommerceMenuBundle\Builder\CategoryTreeBuilder;
use Oro\Bundle\CommerceMenuBundle\Builder\ContentNodeTreeBuilder;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\CommerceMenuBundle\MenuUpdate\Propagator\ToMenuUpdate\SyntheticPropagator;
use Oro\Bundle\NavigationBundle\Entity\MenuUpdateInterface;
use Oro\Bundle\NavigationBundle\MenuUpdate\Propagator\ToMenuUpdate\MenuItemToMenuUpdatePropagatorInterface;
use Oro\Bundle\NavigationBundle\Tests\Unit\MenuItemTestTrait;

class SyntheticPropagatorTest extends \PHPUnit\Framework\TestCase
{
    use MenuItemTestTrait;

    private SyntheticPropagator $propagator;

    protected function setUp(): void
    {
        $this->propagator = new SyntheticPropagator();
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

    public function propagateFromMenuItemDataProvider(): array
    {
        $menuUpdate = (new MenuUpdate())
            ->setKey('sample_item')
            ->setOriginKey('sample_parent');

        return [
            'not synthetic, not content node tree item, not category tree item' => [
                'menuUpdate' => $menuUpdate,
                'menuItem' => $this->createItem($menuUpdate->getKey()),
                'expected' => clone $menuUpdate,
            ],
            'synthetic, no parent' => [
                'menuUpdate' => $menuUpdate,
                'menuItem' => $this->createItem($menuUpdate->getKey())
                    ->setExtra(MenuUpdateInterface::IS_SYNTHETIC, true),
                'expected' => (clone $menuUpdate)
                    ->setSynthetic(true),
            ],
            'synthetic, parent not changed' => [
                'menuUpdate' => (clone $menuUpdate),
                'menuItem' => $this->createItem($menuUpdate->getKey())
                    ->setParent($this->createItem($menuUpdate->getOriginKey()))
                    ->setExtra(MenuUpdateInterface::IS_SYNTHETIC, true),
                'expected' => (clone $menuUpdate)
                    ->setSynthetic(false),
            ],
            'synthetic, parent changed' => [
                'menuUpdate' => (clone $menuUpdate),
                'menuItem' => $this->createItem($menuUpdate->getKey())
                    ->setParent($this->createItem('new_parent'))
                    ->setExtra(MenuUpdateInterface::IS_SYNTHETIC, true),
                'expected' => (clone $menuUpdate)
                    ->setSynthetic(true),
            ],
            'content node tree item, parent not changed' => [
                'menuUpdate' => (clone $menuUpdate),
                'menuItem' => $this->createItem($menuUpdate->getKey())
                    ->setParent($this->createItem($menuUpdate->getOriginKey()))
                    ->setExtra(ContentNodeTreeBuilder::IS_TREE_ITEM, true),
                'expected' => (clone $menuUpdate)
                    ->setSynthetic(false),
            ],
            'content node tree item, parent changed' => [
                'menuUpdate' => (clone $menuUpdate),
                'menuItem' => $this->createItem($menuUpdate->getKey())
                    ->setParent($this->createItem('new_parent'))
                    ->setExtra(ContentNodeTreeBuilder::IS_TREE_ITEM, true),
                'expected' => (clone $menuUpdate)
                    ->setSynthetic(true),
            ],
            'category tree item, parent not changed' => [
                'menuUpdate' => (clone $menuUpdate),
                'menuItem' => $this->createItem($menuUpdate->getKey())
                    ->setParent($this->createItem($menuUpdate->getOriginKey()))
                    ->setExtra(CategoryTreeBuilder::IS_TREE_ITEM, true),
                'expected' => (clone $menuUpdate)
                    ->setSynthetic(false),
            ],
            'category tree item, parent changed' => [
                'menuUpdate' => (clone $menuUpdate),
                'menuItem' => $this->createItem($menuUpdate->getKey())
                    ->setParent($this->createItem('new_parent'))
                    ->setExtra(CategoryTreeBuilder::IS_TREE_ITEM, true),
                'expected' => (clone $menuUpdate)
                    ->setSynthetic(true),
            ],
        ];
    }
}
