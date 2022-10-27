<?php

namespace Oro\Bundle\CustomerBundle\Form\EventListener;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserSettings;
use Oro\Bundle\CustomerBundle\Form\Extension\PreferredLocalizationCustomerUserExtension;
use Oro\Bundle\LocaleBundle\DependencyInjection\Configuration;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * This subscriber processed submitted preferred localization form data and update it's availability.
 */
class PreferredLocalizationCustomerUserSubscriber implements EventSubscriberInterface
{
    /**
     * @var WebsiteManager
     */
    protected $websiteManager;

    /**
     * @var ConfigManager
     */
    protected $configManager;

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    public function __construct(
        WebsiteManager $websiteManager,
        ConfigManager $configManager,
        ManagerRegistry $registry
    ) {
        $this->websiteManager = $websiteManager;
        $this->configManager = $configManager;
        $this->registry = $registry;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::POST_SET_DATA => 'onPostSetData',
            FormEvents::POST_SUBMIT => 'onPostSubmit',
        ];
    }

    public function onPostSetData(FormEvent $event)
    {
        $form = $event->getForm();
        if (!$form->has(PreferredLocalizationCustomerUserExtension::PREFERRED_LOCALIZATION_FIELD)) {
            return;
        }

        if (!$this->isAvailable()) {
            $form->remove(PreferredLocalizationCustomerUserExtension::PREFERRED_LOCALIZATION_FIELD);

            return;
        }

        $form
            ->get(PreferredLocalizationCustomerUserExtension::PREFERRED_LOCALIZATION_FIELD)
            ->setData($this->getPreferredLocalization($event->getData()));
    }

    public function onPostSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        if (!$form->has(PreferredLocalizationCustomerUserExtension::PREFERRED_LOCALIZATION_FIELD)) {
            return;
        }

        $preferredLocalization = $form
            ->get(PreferredLocalizationCustomerUserExtension::PREFERRED_LOCALIZATION_FIELD)
            ->getData();

        /** @var CustomerUser $customerUser */
        $customerUser = $event->getData();
        $website = $customerUser->getWebsite() ?? $this->websiteManager->getDefaultWebsite();
        $customerUserSettingsByWebsite = $customerUser->getWebsiteSettings($website);
        if ($customerUserSettingsByWebsite) {
            $customerUserSettingsByWebsite->setLocalization($preferredLocalization);
        } else {
            $customerUserSettingsByWebsite = new CustomerUserSettings($website);
            $customerUserSettingsByWebsite->setLocalization($preferredLocalization);
            $customerUser->setWebsiteSettings($customerUserSettingsByWebsite);
        }
    }

    protected function getPreferredLocalization(CustomerUser $customerUser): ?Localization
    {
        $website = $customerUser->getWebsite() ?? $this->websiteManager->getDefaultWebsite();

        $preferredLocalization = null;
        $customerUserSettingsByWebsite = $customerUser->getWebsiteSettings($website);
        if ($customerUserSettingsByWebsite) {
            $preferredLocalization = $customerUserSettingsByWebsite->getLocalization();
        }

        return $preferredLocalization;
    }

    protected function isAvailable(): bool
    {
        $enabledLocalizationIds = (array) $this->configManager->get(
            Configuration::getConfigKeyByName(Configuration::ENABLED_LOCALIZATIONS)
        );

        return count($enabledLocalizationIds) > 1;
    }
}
