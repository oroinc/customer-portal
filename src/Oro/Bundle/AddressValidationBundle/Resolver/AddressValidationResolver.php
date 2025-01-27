<?php

namespace Oro\Bundle\AddressValidationBundle\Resolver;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\AddressValidationBundle\Provider\AddressValidationTransportProvider;
use Oro\Bundle\AddressValidationBundle\Resolver\Factory\AddressValidationResolverFactoryInterface;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureCheckerAwareInterface;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureCheckerHolderTrait;

/**
 * Validates the specified address and returns suggested addresses.
 */
class AddressValidationResolver implements AddressValidationResolverInterface, FeatureCheckerAwareInterface
{
    use FeatureCheckerHolderTrait;

    public function __construct(
        private AddressValidationTransportProvider $addressValidationTransportProvider,
        private AddressValidationResolverFactoryInterface $addressValidationResolverFactory
    ) {
    }

    public function resolve(AbstractAddress $address): array
    {
        if (!$this->isFeaturesEnabled()) {
            return [];
        }

        $transport = $this->addressValidationTransportProvider->getAddressValidationTransport();
        if ($transport === null) {
            return [];
        }

        if (!$this->addressValidationResolverFactory->isSupported($transport)) {
            return [];
        }

        return $this->addressValidationResolverFactory
            ->createForTransport($transport)
            ->resolve($address);
    }
}
