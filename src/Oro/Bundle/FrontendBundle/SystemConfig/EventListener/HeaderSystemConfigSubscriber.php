<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\SystemConfig\EventListener;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CMSBundle\Entity\ContentBlock;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\ConfigBundle\Event\ConfigSettingsUpdateEvent;
use Oro\Bundle\FrontendBundle\DependencyInjection\Configuration;

/**
 * Normalize and denormalize part of data displayed in Theme Header section.
 * FormPreSet event is used to transform object identifiers to objects to use for select inputs as current value.
 * BeforeSave event transforms the selected objects to their identifiers to store to storage.
 */
class HeaderSystemConfigSubscriber
{
    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function formPreSet(ConfigSettingsUpdateEvent $event): void
    {
        $settings = $event->getSettings();
        $settings = $this->formPreSetPromotionalContent($settings);
        $event->setSettings($settings);
    }

    public function beforeSave(ConfigSettingsUpdateEvent $event): void
    {
        $settings = $event->getSettings();
        $settings = $this->beforeSavePromotionalContent($settings);
        $event->setSettings($settings);
    }

    private function formPreSetPromotionalContent(array $settings): array
    {
        $promoContentKey = Configuration::getConfigKeyByName(
            Configuration::PROMOTIONAL_CONTENT,
            ConfigManager::SECTION_VIEW_SEPARATOR
        );

        if (\array_key_exists($promoContentKey, $settings)) {
            $value = $settings[$promoContentKey][ConfigManager::VALUE_KEY] ?? null;
            if ($value) {
                $value = $this->doctrine->getManagerForClass(ContentBlock::class)->find(ContentBlock::class, $value);
                $settings[$promoContentKey][ConfigManager::VALUE_KEY] = $value;
            }
        }

        return $settings;
    }

    private function beforeSavePromotionalContent(array $settings): array
    {
        $promoContentKey = Configuration::getConfigKeyByName(Configuration::PROMOTIONAL_CONTENT);
        if (\array_key_exists($promoContentKey, $settings)) {
            $value = $settings[$promoContentKey][ConfigManager::VALUE_KEY];

            if ($value instanceof ContentBlock) {
                $settings[$promoContentKey][ConfigManager::VALUE_KEY] = $value->getId();
            }
        }

        return $settings;
    }
}
