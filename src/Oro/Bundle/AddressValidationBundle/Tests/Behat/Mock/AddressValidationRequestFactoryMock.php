<?php

namespace Oro\Bundle\AddressValidationBundle\Tests\Behat\Mock;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\AddressValidationBundle\Client\Request\AddressValidationRequest;
use Oro\Bundle\AddressValidationBundle\Client\Request\AddressValidationRequestInterface;
use Oro\Bundle\AddressValidationBundle\Client\Request\Factory\AddressValidationRequestFactoryInterface;

class AddressValidationRequestFactoryMock implements AddressValidationRequestFactoryInterface
{
    public function __construct(
        private readonly AddressValidationRequestFactoryInterface $addressValidationRequestFactory
    ) {
    }

    public function create(AbstractAddress $address): AddressValidationRequestInterface
    {
        $originalRequest = $this->addressValidationRequestFactory->create($address);

        $requestData = $originalRequest->getRequestData();

        $uri = $this->resolveUri($address) ?: $originalRequest->getUri();

        return new AddressValidationRequest($uri, $requestData);
    }

    private function resolveUri(AbstractAddress $address): ?string
    {
        if (str_contains($address->getStreet(), 'expanded-view')) {
            return 'expanded-view';
        }

        if (str_contains($address->getStreet(), 'short-view')) {
            return 'short-view';
        }

        if (str_contains($address->getStreet(), 'no-suggests')) {
            return 'no-suggests';
        }

        return null;
    }
}
