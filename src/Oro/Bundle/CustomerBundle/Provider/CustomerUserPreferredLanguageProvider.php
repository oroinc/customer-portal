<?php

namespace Oro\Bundle\CustomerBundle\Provider;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\FrontendLocalizationBundle\Manager\UserLocalizationManager;
use Oro\Bundle\LocaleBundle\DependencyInjection\Configuration;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\LocaleBundle\Provider\BasePreferredLanguageProvider;

/**
 * Returns preferred language for CustomerUser entity based on customer user settings on frontend.
 */
class CustomerUserPreferredLanguageProvider extends BasePreferredLanguageProvider
{
    /**
     * @var UserLocalizationManager
     */
    private $userLocalizationManager;

    /**
     * @param UserLocalizationManager $userLocalizationManager
     */
    public function __construct(UserLocalizationManager $userLocalizationManager)
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
     * {@inheritdoc}
     */
    public function getPreferredLanguageForEntity($entity): string
    {
        /** @var CustomerUser $entity */
        $localization = $this->getLocalizationByCurrentWebsite($entity)
            ?? $this->getLocalizationByPrimaryWebsite($entity);

        return $localization ? $localization->getLanguageCode() : Configuration::DEFAULT_LANGUAGE;
    }

    /**
     * @param CustomerUser $entity
     * @return null|Localization
     */
    private function getLocalizationByCurrentWebsite($entity): ?Localization
    {
        return $this->userLocalizationManager->getCurrentLocalizationByCustomerUser($entity);
    }

    /**
     * @param CustomerUser $customerUser
     * @return null|Localization
     */
    private function getLocalizationByPrimaryWebsite(CustomerUser $customerUser): ?Localization
    {
        if (!$customerUser->getWebsite()) {
            return null;
        }

        return $this->userLocalizationManager->getCurrentLocalizationByCustomerUser(
            $customerUser,
            $customerUser->getWebsite()
        );
    }
}
