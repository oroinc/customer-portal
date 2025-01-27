<?php

declare(strict_types=1);

namespace Oro\Bundle\AddressValidationBundle\Resolver\Factory;

use Oro\Bundle\AddressValidationBundle\Client\AddressValidationClientInterface;
use Oro\Bundle\AddressValidationBundle\Client\Request\Factory\AddressValidationRequestFactoryInterface;
use Oro\Bundle\AddressValidationBundle\ResolvedAddress\Factory\ResolvedAddressFactoryInterface;
use Oro\Bundle\AddressValidationBundle\Resolver\AddressValidationBasicResolver;
use Oro\Bundle\AddressValidationBundle\Resolver\AddressValidationResolverInterface;
use Oro\Bundle\IntegrationBundle\Entity\Transport;

/**
 * Creates a basic address validation resolver.
 */
class AddressValidationResolverBasicFactory implements AddressValidationResolverFactoryInterface
{
    public function __construct(
        private AddressValidationRequestFactoryInterface $addressValidationRequestFactory,
        private AddressValidationClientInterface $addressValidationClient,
        private ResolvedAddressFactoryInterface $resolvedAddressFactory,
        private string $supportedTransport
    ) {
    }

    public function createForTransport(Transport $transport): AddressValidationResolverInterface
    {
        return new AddressValidationBasicResolver(
            $this->addressValidationRequestFactory,
            $this->addressValidationClient,
            $this->resolvedAddressFactory,
            $transport
        );
    }

    public function isSupported(Transport $transport): bool
    {
        return $transport instanceof $this->supportedTransport;
    }
}
