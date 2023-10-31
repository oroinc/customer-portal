<?php

namespace Oro\Bundle\WebsiteBundle\EventListener;

use Oro\Bundle\ConfigBundle\Config\GlobalScopeManager;
use Oro\Bundle\ConfigBundle\Event\ConfigSettingsFormOptionsEvent;

/**
 * Removes "Use Default" checkbox for the following fields on the application configuration level:
 * * oro_website.url
 * * oro_website.secure_url
 */
class RoutingSystemConfigFormOptionsListener
{
    public function onFormOptions(ConfigSettingsFormOptionsEvent $event): void
    {
        if ($event->getConfigManager()->getScopeEntityName() === GlobalScopeManager::SCOPE_NAME) {
            $this->makeFieldNotResettableIfExists($event, 'oro_website.url');
            $this->makeFieldNotResettableIfExists($event, 'oro_website.secure_url');
        }
    }

    private function makeFieldNotResettableIfExists(ConfigSettingsFormOptionsEvent $event, string $configKey): void
    {
        if ($event->hasFormOptions($configKey)) {
            $formOption = $event->getFormOptions($configKey);
            $formOption['resettable'] = false;
            $event->setFormOptions($configKey, $formOption);
        }
    }
}
