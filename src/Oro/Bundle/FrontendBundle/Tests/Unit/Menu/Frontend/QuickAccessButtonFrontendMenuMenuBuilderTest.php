<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Menu\Frontend;

use Knp\Menu\ItemInterface;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\ConfigBundle\Tests\Functional\Traits\ConfigManagerAwareTestTrait;
use Oro\Bundle\FrontendBundle\Menu\Frontend\QuickAccessButtonFrontendMenuMenuBuilder;
use Oro\Bundle\FrontendBundle\Model\QuickAccessButtonConfig;
use Oro\Bundle\NavigationBundle\Menu\BuilderInterface;
use PHPUnit\Framework\MockObject\MockObject;

class QuickAccessButtonFrontendMenuMenuBuilderTest extends \PHPUnit\Framework\TestCase
{
    use ConfigManagerAwareTestTrait;

    private ConfigManager|MockObject $configManager;
    private BuilderInterface|MockObject $menuBuilder;
    private QuickAccessButtonFrontendMenuMenuBuilder $builder;

    protected function setUp(): void
    {
        $this->configManager = $this->createMock(ConfigManager::class);
        $this->menuBuilder = $this->createMock(BuilderInterface::class);
        $this->builder = new QuickAccessButtonFrontendMenuMenuBuilder(
            $this->configManager,
            $this->menuBuilder,
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
                    ->setType(QuickAccessButtonConfig::TYPE_MENU)
                    ->setMenu('test_menu')
            );

        $this->menuBuilder
            ->expects(self::once())
            ->method('build')
            ->with($menuItem, [
                'check_access_not_logged_in' => true,
                'test' => 'test',
            ], 'test_menu');

        $menuItem->expects(self::once())->method('setLabel')->with('test_menu');
        $menuItem->expects(self::once())->method('getUri')->willReturn('test_uri');
        $menuItem->expects(self::once())->method('getChildren')->willReturn([]);

        $this->builder->build($menuItem, ['test' => 'test'], 'quick_access_button_menu');
    }

    public function testBuildWithUnsupportedAlias(): void
    {
        $menuItem = $this->createMock(ItemInterface::class);

        $this->configManager->expects(self::never())->method(self::anything());
        $this->menuBuilder->expects(self::never())->method(self::anything());
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
                    ->setType(QuickAccessButtonConfig::TYPE_WEB_CATALOG_NODE)
                    ->setMenu('test_menu')
            );
        $this->menuBuilder->expects(self::never())->method(self::anything());
        $menuItem->expects(self::never())->method(self::anything());

        $this->builder->build($menuItem, ['test' => 'test'], 'quick_access_button_menu');
    }

    public function testBuildWithIncorrectMenuType(): void
    {
        $menuItem = $this->createMock(ItemInterface::class);

        $this->configManager
            ->expects(self::once())
            ->method('get')
            ->with('oro_frontend.quick_access_button')
            ->willReturn(
                (new QuickAccessButtonConfig())
                    ->setType(QuickAccessButtonConfig::TYPE_MENU)
                    ->setMenu('quick_access_button_menu')
            );
        $this->menuBuilder->expects(self::never())->method(self::anything());
        $menuItem->expects(self::once())->method('setExtra')->with('menu_not_resolved', true);

        $this->builder->build($menuItem, ['test' => 'test'], 'quick_access_button_menu');
    }

    public function testBuildWithIncorrectMenuItemUriIfNoChildren(): void
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

        $this->menuBuilder
            ->expects(self::once())
            ->method('build')
            ->with($menuItem, [
                'check_access_not_logged_in' => true,
                'test' => 'test',
            ], 'test_menu');

        $menuItem->expects(self::once())->method('setLabel')->with('test_menu');
        $menuItem->expects(self::once())->method('getUri')->willReturn(null);
        $menuItem->expects(self::once())->method('getChildren')->willReturn([]);
        $menuItem->expects(self::once())->method('setExtra')->with('menu_not_resolved', true);

        $this->builder->build($menuItem, ['test' => 'test'], 'quick_access_button_menu');
    }
}
