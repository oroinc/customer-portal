<?php

namespace Oro\Bundle\FrontendBundle\Layout\Extension;

use Oro\Bundle\LayoutBundle\Layout\Extension\ThemeConfiguration;
use Oro\Bundle\ThemeBundle\Provider\ThemeConfigurationProvider;
use Oro\Component\Layout\ContextConfiguratorInterface;
use Oro\Component\Layout\ContextInterface;
use Symfony\Component\OptionsResolver\Options;

/**
 * Adds "standalone_main_menu" option to the layout context.
 * Adds "language_and_currency_switchers_above_header" option to the layout context.
 */
class StandaloneMainMenuContextConfigurator implements ContextConfiguratorInterface
{
    public function __construct(private ThemeConfigurationProvider $themeConfigurationProvider)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function configureContext(ContextInterface $context): void
    {
        $context->getResolver()
            ->setDefaults([
                'standalone_main_menu' => function (Options $options) {
                    $optionKey = ThemeConfiguration::buildOptionKey('header', 'standalone_main_menu');

                    return $this
                        ->themeConfigurationProvider
                        ->getThemeConfigurationOption($optionKey);
                },
                'language_and_currency_switchers_above_header' => function (Options $options) {
                    $optionKey = ThemeConfiguration::buildOptionKey('header', 'language_and_currency_switchers');

                    return $this
                            ->themeConfigurationProvider
                            ->getThemeConfigurationOption($optionKey) === 'above_header';
                }
            ])
            ->setAllowedTypes('standalone_main_menu', ['boolean']);
    }
}
