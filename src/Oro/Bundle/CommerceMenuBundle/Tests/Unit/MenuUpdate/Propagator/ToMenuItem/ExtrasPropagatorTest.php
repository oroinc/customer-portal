<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\MenuUpdate\Propagator\ToMenuItem;

use Doctrine\Common\Collections\ArrayCollection;
use Knp\Menu\ItemInterface;
use Oro\Bundle\AttachmentBundle\Entity\File;
use Oro\Bundle\CatalogBundle\Entity\Category;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUserAgentCondition;
use Oro\Bundle\CommerceMenuBundle\MenuUpdate\Propagator\ToMenuItem\ExtrasPropagator;
use Oro\Bundle\CommerceMenuBundle\Tests\Unit\Entity\Stub\MenuUpdateStub;
use Oro\Bundle\NavigationBundle\Entity\MenuUpdateInterface;
use Oro\Bundle\NavigationBundle\MenuUpdate\Propagator\ToMenuItem\MenuUpdateToMenuItemPropagatorInterface;
use Oro\Bundle\NavigationBundle\MenuUpdate\Propagator\ToMenuUpdate\MenuItemToMenuUpdatePropagatorInterface;
use Oro\Bundle\NavigationBundle\Tests\Unit\MenuItemTestTrait;
use Oro\Bundle\WebCatalogBundle\Entity\ContentNode;

class ExtrasPropagatorTest extends \PHPUnit\Framework\TestCase
{
    use MenuItemTestTrait;

    private ExtrasPropagator $propagator;

    protected function setUp(): void
    {
        $this->propagator = new ExtrasPropagator();
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
            $this->propagator->isApplicable($menuItem, $menuUpdate, $strategy)
        );
    }

    public function isApplicableDataProvider(): array
    {
        return [
            'none' => [
                'strategy' => MenuUpdateToMenuItemPropagatorInterface::STRATEGY_NONE,
                'expected' => false,
            ],
            'basic' => [
                'strategy' => MenuUpdateToMenuItemPropagatorInterface::STRATEGY_BASIC,
                'expected' => true,
            ],
            'full' => [
                'strategy' => MenuUpdateToMenuItemPropagatorInterface::STRATEGY_FULL,
                'expected' => true,
            ],
        ];
    }

    /**
     * @dataProvider isApplicableDataProvider
     */
    public function testIsApplicableWhenNotMenuUpdate(string $strategy): void
    {
        $menuUpdate = $this->createMock(MenuUpdateInterface::class);
        $menuItem = $this->createMock(ItemInterface::class);

        self::assertFalse(
            $this->propagator->isApplicable($menuItem, $menuUpdate, $strategy)
        );
    }

    public function testPropagateFromMenuUpdateWhenNotMenuUpdate(): void
    {
        $menuUpdate = $this->createMock(MenuUpdateInterface::class);

        $menuUpdate
            ->expects(self::never())
            ->method(self::anything());

        $this->propagator->propagateFromMenuUpdate(
            $this->createMock(ItemInterface::class),
            $menuUpdate,
            MenuItemToMenuUpdatePropagatorInterface::STRATEGY_FULL
        );
    }

    /**
     * @dataProvider propagateFromMenuUpdateDataProvider
     */
    public function testPropagateFromMenuUpdate(
        MenuUpdateInterface $menuUpdate,
        ItemInterface $expected
    ): void {
        $menuItem = $this->createItem('sample_item');

        $this->propagator->propagateFromMenuUpdate(
            $menuItem,
            $menuUpdate,
            MenuUpdateToMenuItemPropagatorInterface::STRATEGY_FULL
        );

        self::assertEquals($expected, $menuItem);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function propagateFromMenuUpdateDataProvider(): array
    {
        $menuItem = $this->createItem('sample_item')
            ->setExtra(MenuUpdate::IMAGE, null)
            ->setExtra(MenuUpdate::SCREENS, [])
            ->setExtra(MenuUpdate::CONDITION, null)
            ->setExtra(MenuUpdate::USER_AGENT_CONDITIONS, new ArrayCollection())
            ->setExtra(MenuUpdate::TARGET_CONTENT_NODE, null)
            ->setExtra(MenuUpdate::TARGET_CATEGORY, null)
            ->setExtra(MenuUpdate::SYSTEM_PAGE_ROUTE, null);

        $image = new File();
        $menuUserAgentCondition = $this->createMock(MenuUserAgentCondition::class);
        $contentNode = $this->createMock(ContentNode::class);
        $category = $this->createMock(Category::class);

        return [
            'empty' => [
                'menuUpdate' => new MenuUpdateStub(),
                'expected' => clone $menuItem,
            ],
            'with image' => [
                'menuUpdate' => (new MenuUpdateStub())
                    ->setImage($image),
                'expected' => (clone $menuItem)
                    ->setExtra(MenuUpdate::IMAGE, $image),
            ],
            'with screens' => [
                'menuUpdate' => (new MenuUpdateStub())
                    ->setScreens(['sample_screen1', 'sample_screen2']),
                'expected' => (clone $menuItem)
                    ->setExtra(MenuUpdate::SCREENS, ['sample_screen1', 'sample_screen2']),
            ],
            'with condition' => [
                'menuUpdate' => (new MenuUpdateStub())
                    ->setCondition('sample_expr()'),
                'expected' => (clone $menuItem)
                    ->setExtra(MenuUpdate::CONDITION, 'sample_expr()'),
            ],
            'with user agent conditions' => [
                'menuUpdate' => (new MenuUpdateStub())
                    ->addMenuUserAgentCondition($menuUserAgentCondition),
                'expected' => (clone $menuItem)
                    ->setExtra(MenuUpdate::USER_AGENT_CONDITIONS, new ArrayCollection([$menuUserAgentCondition])),
            ],
            'with content node' => [
                'menuUpdate' => (new MenuUpdateStub())
                    ->setContentNode($contentNode)
                    ->setMaxTraverseLevel(3),
                'expected' => (clone $menuItem)
                    ->setExtra(MenuUpdate::TARGET_CONTENT_NODE, $contentNode)
                    ->setExtra(MenuUpdate::MAX_TRAVERSE_LEVEL, 3),
            ],
            'with category' => [
                'menuUpdate' => (new MenuUpdateStub())
                    ->setCategory($category)
                    ->setMaxTraverseLevel(3),
                'expected' => (clone $menuItem)
                    ->setExtra(MenuUpdate::TARGET_CATEGORY, $category)
                    ->setExtra(MenuUpdate::MAX_TRAVERSE_LEVEL, 3),
            ],
            'with system page route' => [
                'menuUpdate' => (new MenuUpdateStub())
                    ->setSystemPageRoute('sample_route'),
                'expected' => (clone $menuItem)
                    ->setExtra(MenuUpdate::SYSTEM_PAGE_ROUTE, 'sample_route'),
            ],
            'with menu template' => [
                'menuUpdate' => (new MenuUpdateStub())
                    ->setMenuTemplate('sample_template'),
                'expected' => (clone $menuItem)
                    ->setExtra(MenuUpdate::MENU_TEMPLATE, 'sample_template'),
            ],
        ];
    }
}
