<?php

namespace Oro\Bundle\FrontendBundle\Form\Configuration;

use Oro\Bundle\FrontendBundle\Form\Type\QuickAccessButtonConfigType;
use Oro\Bundle\FrontendBundle\Model\QuickAccessButtonConfig;
use Oro\Bundle\ThemeBundle\Form\Configuration\AbstractConfigurationChildBuilder;

/**
 * Used to specify type and options for the quick_access_button_config
 */
class QuickAccessButtonConfigBuilder extends AbstractConfigurationChildBuilder
{
    #[\Override] public static function getType(): string
    {
        return 'quick_access_button_config';
    }

    #[\Override] public function supports(array $option): bool
    {
        return $option['type'] === self::getType();
    }

    #[\Override] protected function getTypeClass(): string
    {
        return QuickAccessButtonConfigType::class;
    }

    #[\Override] protected function getConfiguredOptions(array $option): array
    {
        $configuredOptions = parent::getConfiguredOptions($option);

        $configuredOptions['empty_data'] = new QuickAccessButtonConfig();
        $configuredOptions['by_reference'] = false;

        return $configuredOptions;
    }

    protected function getDefaultOptions(): array
    {
        return [];
    }
}
