<?php

namespace Oro\Bundle\AddressValidationBundle\Client\Request\Factory;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\AddressValidationBundle\Client\Request\AddressValidationRequestInterface;

/**
 * Abstraction for the address validation request factory.
 */
interface AddressValidationRequestFactoryInterface
{
    public function create(AbstractAddress $address): AddressValidationRequestInterface;
}
