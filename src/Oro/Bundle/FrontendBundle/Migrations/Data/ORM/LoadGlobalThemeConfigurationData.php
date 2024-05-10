<?php

namespace Oro\Bundle\FrontendBundle\Migrations\Data\ORM;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\LayoutBundle\Layout\Extension\ThemeConfiguration;
use Oro\Bundle\MigrationBundle\Entity\DataFixture;
use Oro\Bundle\ThemeBundle\Migrations\Data\AbstractLoadThemeConfiguration;

/**
 * Load Theme Configurations Data For Global level
 */
class LoadGlobalThemeConfigurationData extends AbstractLoadThemeConfiguration
{
    #[\Override] protected function getConfigManager(): ConfigManager
    {
        return $this->container->get('oro_config.global');
    }

    #[\Override] protected function getScopes(): iterable
    {
        return [null];
    }

    #[\Override] protected function getThemeConfigurationKeys(): array
    {
        return [
            ThemeConfiguration::buildOptionKey('product_listing', 'filters_position') => 'oro_product.filters_position',
            ThemeConfiguration::buildOptionKey('product_details', 'template') => 'oro_frontend.page_templates',
        ];
    }

    #[\Override] protected function isApplicable(): bool
    {
        $dataFixture = $this->manager->getRepository(DataFixture::class)->findOneBy([
            'className' => 'Oro\Bundle\FrontendBundle\Migrations\Data\ORM\LoadGlobalThemeConfiguration'
        ]);

        return !$dataFixture;
    }
}
