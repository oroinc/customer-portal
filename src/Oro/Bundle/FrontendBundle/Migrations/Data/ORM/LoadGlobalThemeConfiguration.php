<?php

namespace Oro\Bundle\FrontendBundle\Migrations\Data\ORM;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\LayoutBundle\Layout\Extension\ThemeConfiguration;
use Oro\Bundle\ThemeBundle\Migrations\Data\AbstractLoadThemeConfiguration;

/**
 * Load Theme Configurations Data For Global level
 */
class LoadGlobalThemeConfiguration extends AbstractLoadThemeConfiguration
{
    protected function getConfigManager(): ConfigManager
    {
        return $this->container->get('oro_config.global');
    }

    protected function getScopes(): iterable
    {
        return [null];
    }

    protected function getThemeConfigurationKeys(): array
    {
        return [
            ThemeConfiguration::buildOptionKey('product_listing', 'filters_position') => 'oro_product.filters_position',
            ThemeConfiguration::buildOptionKey('product_listing', 'template') => 'oro_frontend.page_templates',
        ];
    }
}
