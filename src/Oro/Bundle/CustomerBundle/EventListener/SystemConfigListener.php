<?php

namespace Oro\Bundle\CustomerBundle\EventListener;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\ConfigBundle\Event\ConfigSettingsUpdateEvent;
use Oro\Bundle\CustomerBundle\DependencyInjection\OroCustomerExtension;

class SystemConfigListener
{
    const SETTING = 'default_customer_owner';

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var string
     */
    protected $ownerClass;

    /**
     * @param ManagerRegistry $registry
     * @param string $userClass
     */
    public function __construct(ManagerRegistry $registry, $userClass)
    {
        $this->registry = $registry;
        $this->ownerClass = $userClass;
    }

    public function onFormPreSetData(ConfigSettingsUpdateEvent $event)
    {
        $settingsKey = implode(ConfigManager::SECTION_VIEW_SEPARATOR, [OroCustomerExtension::ALIAS, self::SETTING]);
        $settings = $event->getSettings();
        if (is_array($settings)
            && !empty($settings[$settingsKey]['value'])
        ) {
            $settings[$settingsKey]['value'] = $this->registry
                ->getManagerForClass($this->ownerClass)
                ->find($this->ownerClass, $settings[$settingsKey]['value']);
            $event->setSettings($settings);
        }
    }

    public function onSettingsSaveBefore(ConfigSettingsUpdateEvent $event)
    {
        $settings = $event->getSettings();

        if (!array_key_exists('value', $settings)) {
            return;
        }

        if (!is_a($settings['value'], $this->ownerClass)) {
            return;
        }

        /** @var object $owner */
        $owner = $settings['value'];
        $settings['value'] = $owner->getId();
        $event->setSettings($settings);
    }
}
