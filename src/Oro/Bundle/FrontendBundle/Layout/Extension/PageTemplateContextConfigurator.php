<?php

namespace Oro\Bundle\FrontendBundle\Layout\Extension;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Component\Layout\ContextConfiguratorInterface;
use Oro\Component\Layout\ContextInterface;
use Symfony\Component\OptionsResolver\Options;

/**
 * Adds "page_template" option to the layout context.
 */
class PageTemplateContextConfigurator implements ContextConfiguratorInterface
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
                'page_template' => function (Options $options, $value) {
                    if (!$value) {
                        $pageTemplates = $this->configManager->get('oro_frontend.page_templates');
                        $routeName = $options['route_name'];
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
