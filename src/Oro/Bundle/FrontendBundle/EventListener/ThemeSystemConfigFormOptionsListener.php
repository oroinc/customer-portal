<?php

namespace Oro\Bundle\FrontendBundle\EventListener;

use Oro\Bundle\ConfigBundle\Event\ConfigSettingsFormOptionsEvent;

/**
 * Adds "oro_frontend_page_template_form_field" block prefix to the "oro_frontend.page_templates" field
 * to be able to customize TWIG template for this field.
 */
class ThemeSystemConfigFormOptionsListener
{
    public function onFormOptions(ConfigSettingsFormOptionsEvent $event): void
    {
        $configKey = 'oro_frontend.page_templates';
        if ($event->hasFormOptions($configKey)) {
            $formOption = $event->getFormOptions($configKey);
            $formOption['block_prefix'] = 'oro_frontend_page_template_form_field';
            $event->setFormOptions($configKey, $formOption);
        }
    }
}
