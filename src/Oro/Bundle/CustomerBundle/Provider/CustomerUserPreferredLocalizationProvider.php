<?php

namespace Oro\Bundle\CustomerBundle\Provider;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserSettings;
use Oro\Bundle\FrontendLocalizationBundle\Manager\UserLocalizationManagerInterface;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\LocaleBundle\Provider\AbstractPreferredLocalizationProvider;

/**
 * Returns preferred localization for CustomerUser entity based on customer user settings.
 */
class CustomerUserPreferredLocalizationProvider extends AbstractPreferredLocalizationProvider
{
    private UserLocalizationManagerInterface $userLocalizationManager;

    public function __construct(UserLocalizationManagerInterface $userLocalizationManager)
    {
        $this->userLocalizationManager = $userLocalizationManager;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($entity): bool
    {
        return $entity instanceof CustomerUser && !$entity->isGuest();
    }

    /**
     * @param CustomerUser $entity
     * @return Localization|null
     */
    protected function getPreferredLocalizationForEntity($entity): ?Localization
    {
        /**
         * There can be only one website configuration for the CE version as it is not allowed to have more than one
         * website.
         *
         * @var CustomerUserSettings $settings
         */
        $website = null;
        if (!$entity->getSettings()->isEmpty()) {
            $website = $entity->getSettings()->first()->getWebsite();
        }

        return $this->userLocalizationManager->getCurrentLocalizationByCustomerUser($entity, $website);
    }
}
