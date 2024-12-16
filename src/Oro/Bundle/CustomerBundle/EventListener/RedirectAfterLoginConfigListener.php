<?php

namespace Oro\Bundle\CustomerBundle\EventListener;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CatalogBundle\Entity\Category;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\ConfigBundle\Event\ConfigSettingsUpdateEvent;
use Oro\Bundle\ConfigBundle\Utils\TreeUtils;
use Oro\Bundle\CustomerBundle\DependencyInjection\Configuration;
use Oro\Bundle\WebCatalogBundle\Entity\ContentNode;

/**
 * Responsible for converting data before saving and presetting redirect_after_login system config.
 */
class RedirectAfterLoginConfigListener
{
    public function __construct(private ManagerRegistry $doctrine)
    {
    }

    public function formPreSet(ConfigSettingsUpdateEvent $event): void
    {
        $settingKey = TreeUtils::getConfigKey(
            Configuration::ROOT_NODE,
            Configuration::REDIRECT_AFTER_LOGIN,
            ConfigManager::SECTION_VIEW_SEPARATOR
        );
        $settings = $event->getSettings();

        if (!isset($settings[$settingKey]['value'])) {
            return;
        }

        $settings[$settingKey]['value'] = $this->convertFromSaved($settings[$settingKey]['value']);
        $event->setSettings($settings);
    }

    public function beforeSave(ConfigSettingsUpdateEvent $event): void
    {
        $settings = $event->getSettings();
        if (!\array_key_exists('value', $settings)) {
            return;
        }

        $settings['value'] = $this->convertBeforeSave($settings['value']);
        $event->setSettings($settings);
    }

    private function convertFromSaved(array $value): array
    {
        if (isset($value['category'])) {
            $value['category'] = $this->doctrine->getRepository(Category::class)->find($value['category']);
        }

        if (isset($value['contentNode'])) {
            $value['contentNode'] = $this->doctrine->getRepository(ContentNode::class)->find($value['contentNode']);
        }

        return $value;
    }

    private function convertBeforeSave(array $value): array
    {
        if ($value['category'] ?? null instanceof Category) {
            $value['category'] = $value['category']->getId();
        }

        if ($value['contentNode'] ?? null instanceof ContentNode) {
            $value['contentNode'] = $value['contentNode']->getId();
        }

        return $value;
    }
}
