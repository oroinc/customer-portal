<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Menu\Frontend;

use Knp\Menu\ItemInterface;
use Knp\Menu\MenuFactory;
use Knp\Menu\MenuItem;
use Oro\Bundle\FrontendBundle\Menu\Frontend\QuickAccessButtonFrontendMenuMenuBuilder;
use Oro\Bundle\FrontendBundle\Model\QuickAccessButtonConfig;
use Oro\Bundle\NavigationBundle\Menu\BuilderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class QuickAccessButtonFrontendMenuMenuBuilderTest extends TestCase
{
    private BuilderInterface|MockObject $menuBuilder;
    private QuickAccessButtonFrontendMenuMenuBuilder $builder;

    #[\Override]
    protected function setUp(): void
    {
        $this->menuBuilder = $this->createMock(BuilderInterface::class);
        $this->builder = new QuickAccessButtonFrontendMenuMenuBuilder(
            $this->menuBuilder,
        );
    }

    public function testBuild(): void
    {
        $menuItem = $this->createMock(ItemInterface::class);

        $config = (new QuickAccessButtonConfig())
            ->setType(QuickAccessButtonConfig::TYPE_MENU)
            ->setMenu('test_menu');
        $this->menuBuilder
            ->expects(self::once())
            ->method('build')
            ->with($menuItem, [
                'check_access_not_logged_in' => true,
                'test' => 'test',
                'qab_config' => $config,
            ], 'test_menu');

        $menuItem->expects(self::once())->method('getUri')->willReturn('test_uri');
        $menuItem->expects(self::once())->method('getChildren')->willReturn([]);

        $this->builder->build($menuItem, [
            'test' => 'test',
            'qab_config' => $config,
        ], 'quick_access_button_menu');
    }

    public function testBuildWithUnsupportedAlias(): void
    {
        $menuItem = $this->createMock(ItemInterface::class);

        $this->menuBuilder->expects(self::never())->method(self::anything());
        $menuItem->expects(self::never())->method(self::anything());

        $this->builder->build($menuItem, ['test' => 'test'], 'non_supported');
    }

    public function testBuildWithUnsupportedType(): void
    {
        $menuItem = $this->createMock(ItemInterface::class);
        $config = (new QuickAccessButtonConfig())
            ->setType(QuickAccessButtonConfig::TYPE_WEB_CATALOG_NODE)
            ->setMenu('test_menu');

        $this->menuBuilder->expects(self::never())->method(self::anything());
        $menuItem->expects(self::never())->method(self::anything());

        $this->builder->build($menuItem, [
            'test' => 'test',
            'qab_config' => $config,
        ], 'quick_access_button_menu');
    }

    public function testBuildWithIncorrectMenuType(): void
    {
        $menuItem = $this->createMock(ItemInterface::class);

        $config = (new QuickAccessButtonConfig())
            ->setType(QuickAccessButtonConfig::TYPE_MENU)
            ->setMenu('quick_access_button_menu');
        $this->menuBuilder->expects(self::never())->method(self::anything());
        $menuItem->expects(self::once())->method('setExtra')->with('menu_not_resolved', true);

        $this->builder->build($menuItem, [
            'test' => 'test',
            'qab_config' => $config,
        ], 'quick_access_button_menu');
    }

    public function testBuildWithIncorrectMenuItemUriIfNoChildren(): void
    {
        $menuItem = $this->createMock(ItemInterface::class);

        $config = (new QuickAccessButtonConfig())
            ->setType(QuickAccessButtonConfig::TYPE_MENU)
            ->setMenu('test_menu');

        $this->menuBuilder
            ->expects(self::once())
            ->method('build')
            ->with($menuItem, [
                'check_access_not_logged_in' => true,
                'test' => 'test',
                'qab_config' => $config,
            ], 'test_menu');

        $menuItem->expects(self::once())->method('getUri')->willReturn(null);
        $menuItem->expects(self::once())->method('getChildren')->willReturn([]);
        $menuItem->expects(self::once())->method('setExtra')->with('menu_not_resolved', true);

        $this->builder->build($menuItem, [
            'test' => 'test',
            'qab_config' => $config,
        ], 'quick_access_button_menu');
    }

    public function testBuildWithNestingLevelDefault(): void
    {
        $menuFactory = new MenuFactory();
        $menuItem = new MenuItem('test', $menuFactory);
        $menuItem->setDisplay(true);
        $menuItem->setDisplayChildren(true);

        $menuItemLevel1 = $menuItem->addChild('level1');
        $menuItemLevel1->setDisplay(true);
        $menuItemLevel1->setDisplayChildren(true);

        $menuItemLevel2 = $menuItemLevel1->addChild('level2');
        $menuItemLevel2->setDisplay(true);
        $menuItemLevel2->setDisplayChildren(true);

        $config = (new QuickAccessButtonConfig())
            ->setType(QuickAccessButtonConfig::TYPE_MENU)
            ->setMenu('test_menu');
        $this->menuBuilder
            ->expects(self::once())
            ->method('build')
            ->with($menuItem, [
                'check_access_not_logged_in' => true,
                'test' => 'test',
                'qab_config' => $config,
            ], 'test_menu')
            ->willReturn($menuItem);

        $this->builder->setMaxNestingLevel(0);
        $this->builder->build($menuItem, [
            'test' => 'test',
            'qab_config' => $config,
        ], 'quick_access_button_menu');

        self::assertEquals(true, $menuItem->isDisplayed());
        self::assertEquals(true, $menuItem->getDisplayChildren());

        self::assertEquals([$menuItemLevel1], array_values($menuItem->getChildren()));
        self::assertEquals([$menuItemLevel2], array_values($menuItemLevel1->getChildren()));

        self::assertEquals(true, $menuItemLevel1->isDisplayed());
        self::assertEquals(false, $menuItemLevel1->getDisplayChildren());

        self::assertEquals(false, $menuItemLevel2->isDisplayed());
        self::assertEquals(false, $menuItemLevel2->getDisplayChildren());
    }

    public function testBuildWithNestingLevelTwoLevels(): void
    {
        $menuFactory = new MenuFactory();
        $menuItem = new MenuItem('test', $menuFactory);
        $menuItem->setDisplay(true);
        $menuItem->setDisplayChildren(true);

        $menuItemLevel1 = $menuItem->addChild('level1');
        $menuItemLevel1->setDisplay(true);
        $menuItemLevel1->setDisplayChildren(true);

        $menuItemLevel2 = $menuItemLevel1->addChild('level2');
        $menuItemLevel2->setDisplay(true);
        $menuItemLevel2->setDisplayChildren(true);

        $config = (new QuickAccessButtonConfig())
            ->setType(QuickAccessButtonConfig::TYPE_MENU)
            ->setMenu('test_menu');
        $this->menuBuilder
            ->expects(self::once())
            ->method('build')
            ->with($menuItem, [
                'check_access_not_logged_in' => true,
                'test' => 'test',
                'qab_config' => $config,
            ], 'test_menu')
            ->willReturn($menuItem);

        $this->builder->setMaxNestingLevel(2);
        $this->builder->build($menuItem, [
            'test' => 'test',
            'qab_config' => $config,
        ], 'quick_access_button_menu');

        self::assertEquals(true, $menuItem->isDisplayed());
        self::assertEquals(true, $menuItem->getDisplayChildren());

        self::assertEquals([$menuItemLevel1], array_values($menuItem->getChildren()));
        self::assertEquals([$menuItemLevel2], array_values($menuItemLevel1->getChildren()));

        self::assertEquals(true, $menuItemLevel1->isDisplayed());
        self::assertEquals(true, $menuItemLevel1->getDisplayChildren());

        self::assertEquals(true, $menuItemLevel2->isDisplayed());
        self::assertEquals(false, $menuItemLevel2->getDisplayChildren());
    }
}
