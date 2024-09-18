<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Layout\DataProvider;

use Knp\Menu\ItemInterface;
use Oro\Bundle\FrontendBundle\Layout\DataProvider\ThemeHeaderConfigProvider;
use Oro\Bundle\FrontendBundle\Model\QuickAccessButtonConfig;
use Oro\Bundle\FrontendBundle\Provider\QuickAccessButtonDataProvider;
use Oro\Bundle\LayoutBundle\Layout\Extension\ThemeConfiguration;
use Oro\Bundle\ThemeBundle\Provider\ThemeConfigurationProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ThemeHeaderConfigProviderTest extends TestCase
{
    private QuickAccessButtonDataProvider|MockObject $quickAccessButtonDataProvider;
    private ThemeConfigurationProvider|MockObject $themeConfigurationProvider;
    private ThemeHeaderConfigProvider $provider;

    protected function setUp(): void
    {
        $this->quickAccessButtonDataProvider = $this->createMock(QuickAccessButtonDataProvider::class);
        $this->themeConfigurationProvider = $this->createMock(ThemeConfigurationProvider::class);

        $this->provider = new ThemeHeaderConfigProvider(
            $this->quickAccessButtonDataProvider,
            $this->themeConfigurationProvider
        );
    }

    public function testGetQuickAccessButton(): void
    {
        $config = new QuickAccessButtonConfig();
        $menuItem = $this->createMock(ItemInterface::class);

        $this->themeConfigurationProvider->expects(self::once())
            ->method('getThemeConfigurationOption')
            ->with(ThemeConfiguration::buildOptionKey('header', 'quick_access_button'))
            ->willReturn($config);
        $this->quickAccessButtonDataProvider->expects(self::once())
            ->method('getMenu')
            ->with(self::identicalTo($config))
            ->willReturn($menuItem);

        self::assertSame($menuItem, $this->provider->getQuickAccessButton());
    }

    public function testGetQuickAccessButtonWhenConfigOptionIsEmpty(): void
    {
        $this->themeConfigurationProvider->expects(self::once())
            ->method('getThemeConfigurationOption')
            ->with(ThemeConfiguration::buildOptionKey('header', 'quick_access_button'))
            ->willReturn(null);
        $this->quickAccessButtonDataProvider->expects(self::never())
            ->method('getMenu');

        self::assertNull($this->provider->getQuickAccessButton());
    }

    public function testGetQuickAccessButtonLabel(): void
    {
        $config = new QuickAccessButtonConfig();
        $label = 'label';

        $this->themeConfigurationProvider->expects(self::once())
            ->method('getThemeConfigurationOption')
            ->with(ThemeConfiguration::buildOptionKey('header', 'quick_access_button'))
            ->willReturn($config);
        $this->quickAccessButtonDataProvider->expects(self::once())
            ->method('getLabel')
            ->with(self::identicalTo($config))
            ->willReturn($label);

        self::assertEquals($label, $this->provider->getQuickAccessButtonLabel());
    }

    public function testGetQuickAccessButtonLabelWhenConfigOptionIsEmpty(): void
    {
        $this->themeConfigurationProvider->expects(self::once())
            ->method('getThemeConfigurationOption')
            ->with(ThemeConfiguration::buildOptionKey('header', 'quick_access_button'))
            ->willReturn(null);
        $this->quickAccessButtonDataProvider->expects(self::never())
            ->method('getLabel');

        self::assertNull($this->provider->getQuickAccessButtonLabel());
    }
}
