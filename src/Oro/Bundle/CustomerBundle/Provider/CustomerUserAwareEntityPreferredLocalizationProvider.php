<?php

namespace Oro\Bundle\CustomerBundle\Provider;

use Oro\Bundle\CustomerBundle\Entity\CustomerOwnerAwareInterface;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\FrontendLocalizationBundle\Manager\UserLocalizationManagerInterface;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\LocaleBundle\Provider\AbstractPreferredLocalizationProvider;

/**
 * Returns preferred localization for a customer user aware entity.
 */
class CustomerUserAwareEntityPreferredLocalizationProvider extends AbstractPreferredLocalizationProvider
{
    public function __construct(
        private UserLocalizationManagerInterface $userLocalizationManager
    ) {
    }

    #[\Override]
    public function supports($entity): bool
    {
        return $entity instanceof CustomerOwnerAwareInterface
            && $entity->getCustomerUser()
            && !$entity->getCustomerUser()->isGuest();
    }

    /**
     * @param CustomerOwnerAwareInterface $entity
     *
     * @return Localization|null
     */
    #[\Override]
    protected function getPreferredLocalizationForEntity($entity): ?Localization
    {
        $customerUser = $entity->getCustomerUser();

        return $this->getLocalizationByCurrentWebsite($customerUser) ??
            $this->getLocalizationByPrimaryWebsite($customerUser);
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
