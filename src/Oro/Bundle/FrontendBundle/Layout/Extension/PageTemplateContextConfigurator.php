<?php

namespace Oro\Bundle\FrontendBundle\Layout\Extension;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\LayoutBundle\Layout\Extension\ThemeConfiguration as LayoutThemeConfiguration;
use Oro\Bundle\ThemeBundle\Provider\ThemeConfigurationProvider;
use Oro\Component\Layout\ContextConfiguratorInterface;
use Oro\Component\Layout\ContextInterface;
use Symfony\Component\OptionsResolver\Options;

/**
 * Adds "page_template" option to the layout context.
 */
class PageTemplateContextConfigurator implements ContextConfiguratorInterface
{
    public function __construct(
        private ConfigManager $configManager,
        private ThemeConfigurationProvider $themeConfigurationProvider
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function configureContext(ContextInterface $context): void
    {
        $context->getResolver()
            ->setDefaults([
                'page_template' => function (Options $options, $value) {
                    if (!$value) {
                        $key = LayoutThemeConfiguration::buildOptionKey('product_details', 'template');
                        if ($this->themeConfigurationProvider->hasThemeConfigurationOption($key)) {
                            return $this->themeConfigurationProvider->getThemeConfigurationOption($key);
                        }

                        $routeName = $options['route_name'];
                        $pageTemplates = $this->configManager->get('oro_frontend.page_templates');
                        if (isset($pageTemplates[$routeName]) && $pageTemplates[$routeName]) {
                            $value = $pageTemplates[$routeName];
                        }
                    }

                    return $value;
                }
            ])
            ->setAllowedTypes('page_template', ['string', 'null']);
    }
}
