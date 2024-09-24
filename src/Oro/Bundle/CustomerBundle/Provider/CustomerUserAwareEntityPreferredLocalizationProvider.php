<?php

namespace Oro\Bundle\CustomerBundle\Provider;

use Oro\Bundle\CustomerBundle\Entity\CustomerOwnerAwareInterface;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\LocaleBundle\Provider\AbstractPreferredLocalizationProvider;
use Oro\Bundle\LocaleBundle\Provider\PreferredLocalizationProviderInterface;

/**
 * Returns preferred localization for a customer user aware entity.
 */
class CustomerUserAwareEntityPreferredLocalizationProvider extends AbstractPreferredLocalizationProvider
{
    private PreferredLocalizationProviderInterface $customerUserPreferredLocalizationProvider;

    public function __construct(PreferredLocalizationProviderInterface $customerUserPreferredLocalizationProvider)
    {
        $this->customerUserPreferredLocalizationProvider = $customerUserPreferredLocalizationProvider;
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
        return $this->customerUserPreferredLocalizationProvider->getPreferredLocalization($entity->getCustomerUser());
    }
}
