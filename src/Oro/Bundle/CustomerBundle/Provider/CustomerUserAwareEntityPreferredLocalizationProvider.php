<?php

namespace Oro\Bundle\CustomerBundle\Provider;

use Oro\Bundle\CustomerBundle\Entity\CustomerOwnerAwareInterface;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\FrontendLocalizationBundle\Manager\UserLocalizationManagerInterface;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\LocaleBundle\Provider\AbstractPreferredLocalizationProvider;
use Oro\Bundle\LocaleBundle\Provider\PreferredLocalizationProviderInterface;

/**
 * Returns preferred localization for a customer user aware entity.
 */
class CustomerUserAwareEntityPreferredLocalizationProvider extends AbstractPreferredLocalizationProvider
{
    private PreferredLocalizationProviderInterface $customerUserPreferredLocalizationProvider;

    private ?UserLocalizationManagerInterface $userLocalizationManager = null;

    public function __construct(PreferredLocalizationProviderInterface $customerUserPreferredLocalizationProvider)
    {
        $this->customerUserPreferredLocalizationProvider = $customerUserPreferredLocalizationProvider;
    }

    public function setUserLocalizationManager(?UserLocalizationManagerInterface $userLocalizationManager): void
    {
        $this->userLocalizationManager = $userLocalizationManager;
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
        // BC layer.
        if (!$this->userLocalizationManager) {
            return $this->customerUserPreferredLocalizationProvider
                ->getPreferredLocalization($entity->getCustomerUser());
        }

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
