<?php

namespace Oro\Bundle\AddressValidationBundle\Resolver;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\AddressValidationBundle\Client\AddressValidationClientInterface;
use Oro\Bundle\AddressValidationBundle\Client\Request\Factory\AddressValidationRequestFactoryInterface;
use Oro\Bundle\AddressValidationBundle\ResolvedAddress\Factory\ResolvedAddressFactoryInterface;
use Oro\Bundle\IntegrationBundle\Entity\Transport;

/**
 * Basic address validation resolver.
 */
class AddressValidationBasicResolver implements AddressValidationResolverInterface
{
    public function __construct(
        private AddressValidationRequestFactoryInterface $addressValidationRequestFactory,
        private AddressValidationClientInterface $addressValidationClient,
        private ResolvedAddressFactoryInterface $resolvedAddressFactory,
        private Transport $transport
    ) {
    }

    public function resolve(AbstractAddress $address): array
    {
        $request = $this->addressValidationRequestFactory->create($address);
        $response = $this->addressValidationClient->send($request, $this->transport);

        $resolvedAddresses = [];
        foreach ($response->getResolvedAddresses() as $rawAddress) {
            $resolvedAddress = $this->resolvedAddressFactory->createResolvedAddress($rawAddress, $address);
            if ($resolvedAddress === null) {
                continue;
            }

            $resolvedAddresses[(string)$resolvedAddress] = $resolvedAddress;
        }

        return array_values($resolvedAddresses);
    }
}
