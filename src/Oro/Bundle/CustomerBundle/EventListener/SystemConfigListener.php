<?php

namespace Oro\Bundle\CustomerBundle\EventListener;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\ConfigBundle\Event\ConfigSettingsUpdateEvent;
use Oro\Bundle\CustomerBundle\DependencyInjection\Configuration;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * Transforms user ID to User entity and vise versa for the "default customer owner" configuration option.
 */
class SystemConfigListener
{
    private const KEY = 'default_customer_owner';

    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function onFormPreSetData(ConfigSettingsUpdateEvent $event): void
    {
        $settingsKey = Configuration::ROOT_NODE . ConfigManager::SECTION_VIEW_SEPARATOR . self::KEY;
        $settings = $event->getSettings();
        if (\is_array($settings) && !empty($settings[$settingsKey]['value'])) {
            $settings[$settingsKey]['value'] = $this->doctrine
                ->getManagerForClass(User::class)
                ->find(User::class, $settings[$settingsKey]['value']);
            $event->setSettings($settings);
        }
    }

    public function onSettingsSaveBefore(ConfigSettingsUpdateEvent $event): void
    {
        $settings = $event->getSettings();
        if (!\array_key_exists('value', $settings)) {
            return;
        }
        if (!$settings['value'] instanceof User) {
            return;
        }

        $settings['value'] = $settings['value']->getId();
        $event->setSettings($settings);
    }
}
