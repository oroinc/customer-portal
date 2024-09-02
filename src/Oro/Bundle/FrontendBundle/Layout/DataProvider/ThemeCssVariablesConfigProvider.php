<?php

namespace Oro\Bundle\FrontendBundle\Layout\DataProvider;

use Oro\Bundle\LayoutBundle\Layout\Extension\ThemeConfiguration;
use Oro\Bundle\ThemeBundle\Provider\ThemeConfigurationProvider;

/**
 * Provides CSS variables from the specified theme sections.
 */
class ThemeCssVariablesConfigProvider
{
    public function __construct(
        private readonly ThemeConfigurationProvider $themeConfigurationProvider,
        private array $sections
    ) {
    }

    public function getStylesVariables(): array
    {
        $items = [];
        $configurationOptions = $this->themeConfigurationProvider->getThemeConfigurationOptions();
        foreach ($configurationOptions as $fullKey => $value) {
            [$group] = explode(ThemeConfiguration::OPTION_KEY_DELIMITER, $fullKey);
            if (!\in_array($group, $this->sections, true) || !$value?->getValue()) {
                continue;
            }
            $items[$value->getVariableName()] = $value->getValue();
        }

        return $items;
    }
}
