<?php

namespace Oro\Bundle\FrontendBundle\Layout\Extension;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Component\Layout\ContextConfiguratorInterface;
use Oro\Component\Layout\ContextInterface;
use Symfony\Component\OptionsResolver\Options;

/**
 * Adds "standalone_main_menu" option to the layout context.
 * Adds "language_and_currency_switchers_above_header" option to the layout context.
 */
class StandaloneMainMenuContextConfigurator implements ContextConfiguratorInterface
{
    private ConfigManager $configManager;

    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    /**
     * {@inheritdoc}
     */
    public function configureContext(ContextInterface $context): void
    {
        $context->getResolver()
            ->setDefaults([
                'standalone_main_menu' => function (Options $options) {
                    return $this->configManager->get('oro_frontend.standalone_main_menu');
                },
                'language_and_currency_switchers_above_header' => function (Options $options) {
                    return $this->configManager->get('oro_frontend.language_and_currency_switchers') === 'above_header';
                }
            ])
            ->setAllowedTypes('standalone_main_menu', ['boolean']);
    }
}
