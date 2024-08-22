<?php

namespace Oro\Bundle\FrontendBundle\Layout\DataProvider;

use Oro\Bundle\LayoutBundle\Layout\Extension\ThemeConfiguration;
use Oro\Bundle\ThemeBundle\Provider\ThemeConfigurationProvider;

/**
 * Provides css variables from the specified theme sections
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

        $themeConfigurationItems = $this->themeConfigurationProvider->getThemeConfiguration()->getConfiguration();

        foreach ($themeConfigurationItems as $fullKey => $value) {
            [$group] = explode(ThemeConfiguration::OPTION_KEY_DELIMITER, $fullKey);

            if (!$this->isAppliedGroup($group) || !$value?->getValue()) {
                continue;
            }

            $items[$value->getVariableName()] = $value->getValue();
        }

        return $items;
    }

    private function isAppliedGroup(string $group): bool
    {
        return in_array($group, $this->sections);
    }
}
