<?php

namespace Oro\Bundle\FrontendBundle\Provider;

use Oro\Bundle\ThemeBundle\Provider\ThemeConfigurationTypeProviderInterface;

/**
 * Provides theme configuration type and label storefront themes.
 */
class StorefrontThemeConfigurationTypeProvider implements ThemeConfigurationTypeProviderInterface
{
    public const string STOREFRONT = 'storefront';

    public function getType(): string
    {
        return static::STOREFRONT;
    }

    public function getLabel(): string
    {
        return 'oro_frontend.theme.themeconfiguration.types.storefront.label';
    }
}
