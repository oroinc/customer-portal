<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Layout\DataProvider;

use Knp\Menu\ItemInterface;
use Oro\Bundle\FrontendBundle\Model\QuickAccessButtonConfig;
use Oro\Bundle\FrontendBundle\Provider\QuickAccessButtonDataProvider;
use Oro\Bundle\LayoutBundle\Layout\Extension\ThemeConfiguration;
use Oro\Bundle\ThemeBundle\Provider\ThemeConfigurationProvider;

/**
 * Layout data provider for Header System Config options.
 */
class ThemeHeaderConfigProvider
{
    public function __construct(
        private QuickAccessButtonDataProvider $quickAccessButtonDataProvider,
        private ThemeConfigurationProvider $themeConfigurationProvider
    ) {
    }

    public function getQuickAccessButton(): ?ItemInterface
    {
        $value = $this->getQuickAccessButtonValue();

        return $value ? $this->quickAccessButtonDataProvider->getMenu($value) : null;
    }

    public function getQuickAccessButtonLabel(): ?string
    {
        $value = $this->getQuickAccessButtonValue();

        return $value ? $this->quickAccessButtonDataProvider->getLabel($value) : null;
    }

    private function getQuickAccessButtonValue(): ?QuickAccessButtonConfig
    {
        return $this->themeConfigurationProvider->getThemeConfigurationOption(
            ThemeConfiguration::buildOptionKey('header', 'quick_access_button')
        );
    }
}
