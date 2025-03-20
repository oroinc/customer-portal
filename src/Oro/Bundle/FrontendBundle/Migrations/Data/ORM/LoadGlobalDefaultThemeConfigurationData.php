<?php

namespace Oro\Bundle\FrontendBundle\Migrations\Data\ORM;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;

/**
 * Load Theme Configurations Data For Global level for default theme
 */
class LoadGlobalDefaultThemeConfigurationData extends LoadGlobalThemeConfigurationData
{
    public const string DEFAULT_THEME = 'default';

    public function getDependencies(): array
    {
        return [
            LoadGlobalThemeConfigurationData::class,
        ];
    }

    #[\Override]
    protected function getFrontendTheme(ConfigManager $configManager, ?object $scope = null): ?string
    {
        return self::DEFAULT_THEME;
    }

    #[\Override]
    protected function isApplicable(): bool
    {
        return true;
    }

    #[\Override]
    protected function isStoreInSystemConfig(): bool
    {
        return false;
    }
}
