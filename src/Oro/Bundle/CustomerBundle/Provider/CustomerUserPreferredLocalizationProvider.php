<?php

namespace Oro\Bundle\CustomerBundle\Provider;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\FrontendLocalizationBundle\Manager\UserLocalizationManagerInterface;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\LocaleBundle\Provider\AbstractPreferredLocalizationProvider;

/**
 * Returns preferred localization for CustomerUser entity based on customer user settings on frontend.
 */
class CustomerUserPreferredLocalizationProvider extends AbstractPreferredLocalizationProvider
{
    /**
     * @var UserLocalizationManagerInterface|null
     */
    private $userLocalizationManager;

    public function __construct(?UserLocalizationManagerInterface $userLocalizationManager)
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
        return $this->getLocalizationByCurrentWebsite($entity) ?? $this->getLocalizationByPrimaryWebsite($entity);
    }

    private function getLocalizationByCurrentWebsite(CustomerUser $entity): ?Localization
    {
        return $this->userLocalizationManager->getCurrentLocalizationByCustomerUser($entity);
    }

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
