<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Builder;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Knp\Menu\MenuItem;
use Oro\Bundle\CommerceMenuBundle\Builder\MenuIconsBuilder;
use Oro\Bundle\FrontendBundle\Provider\StorefrontIconsMappingProvider;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Component\Layout\Extension\Theme\Model\CurrentThemeProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MenuIconsBuilderTest extends TestCase
{
    private StorefrontIconsMappingProvider&MockObject $storefrontIconsMappingProvider;
    private CurrentThemeProvider&MockObject $currentThemeProvider;
    private FrontendHelper&MockObject $frontendHelper;
    private MenuIconsBuilder $builder;

    #[\Override]
    protected function setUp(): void
    {
        $this->storefrontIconsMappingProvider = $this->createMock(StorefrontIconsMappingProvider::class);
        $this->currentThemeProvider = $this->createMock(CurrentThemeProvider::class);
        $this->frontendHelper = $this->createMock(FrontendHelper::class);

        $this->builder = new MenuIconsBuilder(
            $this->storefrontIconsMappingProvider,
            $this->currentThemeProvider,
            $this->frontendHelper
        );
    }

    public function testBuildWhenNotFrontendRequest(): void
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(false);

        $this->currentThemeProvider->expects(self::never())
            ->method(self::anything());

        $this->storefrontIconsMappingProvider->expects(self::never())
            ->method(self::anything());

        $this->builder->build($this->getMenuItem('sample_menu', [], [
            'menu_item_1' => $this->getMenuItem('menu_item_1', [], [], true),
        ], true));
    }

    public function testBuildWhenNoCurrentTheme(): void
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->currentThemeProvider->expects(self::once())
            ->method('getCurrentThemeId')
            ->willReturn(null);

        $this->storefrontIconsMappingProvider->expects(self::never())
            ->method(self::anything());

        $this->builder->build($this->getMenuItem('sample_menu', [], [
            'menu_item_1' => $this->getMenuItem('menu_item_1', [], [], true),
        ], true));
    }

    public function testBuildWhenNotDisplayed(): void
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $currentThemeName = 'sample_theme';
        $this->currentThemeProvider->expects(self::once())
            ->method('getCurrentThemeId')
            ->willReturn($currentThemeName);

        $this->storefrontIconsMappingProvider->expects(self::once())
            ->method('getIconsMappingForTheme')
            ->with($currentThemeName)
            ->willReturn(['fa-sample-icon' => 'svg-sample-icon']);

        $this->builder->build($this->getMenuItem('sample_menu', [], [
            'menu_item_1' => $this->getMenuItem('menu_item_1', [], [], false),
        ], true));
    }

    public function testBuildWhenNoIcon(): void
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $currentThemeName = 'sample_theme';
        $this->currentThemeProvider->expects(self::once())
            ->method('getCurrentThemeId')
            ->willReturn($currentThemeName);

        $this->storefrontIconsMappingProvider->expects(self::once())
            ->method('getIconsMappingForTheme')
            ->with($currentThemeName)
            ->willReturn(['fa-sample-icon' => 'svg-sample-icon']);

        $menuItem = $this->getMenuItem('menu_item_1', [], [], true);

        $this->builder->build($this->getMenuItem('sample_menu', [], [
            'menu_item_1' => $menuItem,
        ], true));

        self::assertNull($menuItem->getExtra('icon'));
    }

    public function testBuildWhenHasIcon(): void
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $currentThemeName = 'sample_theme';
        $this->currentThemeProvider->expects(self::once())
            ->method('getCurrentThemeId')
            ->willReturn($currentThemeName);

        $mapping = ['fa-sample-icon' => 'svg-sample-icon'];
        $this->storefrontIconsMappingProvider->expects(self::once())
            ->method('getIconsMappingForTheme')
            ->with($currentThemeName)
            ->willReturn($mapping);

        $menuItem = $this->getMenuItem('menu_item_1', ['icon' => 'fa-sample-icon'], [], true);

        $this->builder->build($this->getMenuItem('sample_menu', [], [
            'menu_item_1' => $menuItem,
        ], true));

        self::assertEquals($mapping['fa-sample-icon'], $menuItem->getExtra('icon'));
    }

    public function testBuildWhenHasMissingIconWithoutFallbackIcon(): void
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $currentThemeName = 'sample_theme';
        $this->currentThemeProvider->expects(self::once())
            ->method('getCurrentThemeId')
            ->willReturn($currentThemeName);

        $mapping = ['fa-sample-icon' => 'svg-sample-icon'];
        $this->storefrontIconsMappingProvider->expects(self::once())
            ->method('getIconsMappingForTheme')
            ->with($currentThemeName)
            ->willReturn($mapping);

        $menuItem = $this->getMenuItem('menu_item_1', ['icon' => 'missing-icon'], [], true);

        $this->builder->build($this->getMenuItem('sample_menu', [], [
            'menu_item_1' => $menuItem,
        ], true));

        self::assertNull($menuItem->getExtra('icon'));
    }

    public function testBuildWhenHasMissingIconWithFallbackIcon(): void
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $currentThemeName = 'sample_theme';
        $this->currentThemeProvider->expects(self::once())
            ->method('getCurrentThemeId')
            ->willReturn($currentThemeName);

        $mapping = ['fa-sample-icon' => 'svg-sample-icon'];
        $this->storefrontIconsMappingProvider->expects(self::once())
            ->method('getIconsMappingForTheme')
            ->with($currentThemeName)
            ->willReturn($mapping);

        $menuItem = $this->getMenuItem('menu_item_1', ['icon' => 'missing-icon'], [], true);

        $this->builder->setFallbackIcon('fallback-icon');
        $this->builder->build($this->getMenuItem('sample_menu', [], [
            'menu_item_1' => $menuItem,
        ], true));

        self::assertEquals('fallback-icon', $menuItem->getExtra('icon'));
    }

    private function getMenuItem(
        string $menuName,
        array $extras,
        array $children,
        bool $isDisplayed
    ): ItemInterface {
        $factory = $this->createMock(FactoryInterface::class);
        $menuItem = new MenuItem($menuName, $factory);
        $menuItem->setExtras($extras);
        $menuItem->setChildren($children);
        $menuItem->setDisplay($isDisplayed);

        return $menuItem;
    }
}
