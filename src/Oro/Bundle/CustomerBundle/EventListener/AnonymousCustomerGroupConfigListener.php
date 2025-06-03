<?php

namespace Oro\Bundle\CustomerBundle\EventListener;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\ConfigBundle\Event\ConfigSettingsUpdateEvent;
use Oro\Bundle\ConfigBundle\Event\ConfigUpdateEvent;
use Oro\Bundle\CustomerBundle\DependencyInjection\Configuration;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteSearchBundle\Event\ReindexationRequestEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Handles ConfigSettingsUpdateEvent to transform CustomerGroup configuration values.
 *
 * This class ensures:
 * - During form display (onFormPreSetData), replaces the customer group ID
 *   with the actual CustomerGroup entity for proper form handling.
 * - Before saving settings (onSettingsSaveBefore), converts the CustomerGroup
 *   entity back to its ID for configuration persistence.
 */
class AnonymousCustomerGroupConfigListener
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private ManagerRegistry $doctrine,
        private string $configKey
    ) {
    }

    public function onFormPreSetData(ConfigSettingsUpdateEvent $event): void
    {
        $settingsKey = $this->prepareSettingsKey();
        $settings = $event->getSettings();

        $customerGroupId = $settings[$settingsKey]['value'] ?? null;
        if (null === $customerGroupId) {
            return;
        }

        $customerGroup = $this->doctrine->getManagerForClass(CustomerGroup::class)
            ->find(CustomerGroup::class, (int) $customerGroupId);
        $settings[$settingsKey]['value'] = $customerGroup;
        $event->setSettings($settings);
    }

    public function onSettingsSaveBefore(ConfigSettingsUpdateEvent $event): void
    {
        $settings = $event->getSettings();
        $value = $settings[$this->configKey]['value'] ?? null;

        if ($value instanceof CustomerGroup) {
            $settings[$this->configKey]['value'] = $value->getId();
            $event->setSettings($settings);
        }
    }

    public function onUpdateAfter(ConfigUpdateEvent $event): void
    {
        $configName = Configuration::getConfigKeyByName(Configuration::ANONYMOUS_CUSTOMER_GROUP);
        if (!$event->isChanged($configName)) {
            return;
        }

        if ($event->getNewValue($configName) !== $event->getOldValue($configName)) {
            $this->eventDispatcher->dispatch(
                new ReindexationRequestEvent([], $this->getAffectedWebsiteIds($event)),
                ReindexationRequestEvent::EVENT_NAME
            );
        }
    }

    private function getAffectedWebsiteIds(ConfigUpdateEvent $event): array
    {
        $scope = $event->getScope();
        if ('organization' === $scope) {
            $organization = $this->getOrganization($event->getScopeId());

            return $this->doctrine->getRepository(Website::class)->getAllWebsitesIds($organization);
        }

        return [];
    }

    private function getOrganization(int $organizationId): Organization
    {
        return $this->doctrine->getManagerForClass(Organization::class)
            ->getReference(Organization::class, $organizationId);
    }

    private function prepareSettingsKey(): string
    {
        return str_replace(
            ConfigManager::SECTION_MODEL_SEPARATOR,
            ConfigManager::SECTION_VIEW_SEPARATOR,
            $this->configKey
        );
    }
}
