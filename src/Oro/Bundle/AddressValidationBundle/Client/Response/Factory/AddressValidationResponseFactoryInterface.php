<?php

namespace Oro\Bundle\AddressValidationBundle\Client\Response\Factory;

use Oro\Bundle\AddressValidationBundle\Client\Response\AddressValidationResponseInterface;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Client\RestResponseInterface;

/**
 * Abstraction for the address validation response factory.
 */
interface AddressValidationResponseFactoryInterface
{
    public function create(RestResponseInterface $response): AddressValidationResponseInterface;

    public function createExceptionResult(\Exception $exception): AddressValidationResponseInterface;
}
