<?php

declare(strict_types=1);

namespace Oro\Bundle\AddressValidationBundle\ResolvedAddress\Factory;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\AddressValidationBundle\Model\ResolvedAddress;

/**
 * Creates {@see ResolvedAddress} for the specified raw address data coming from an address validation response.
 */
interface ResolvedAddressFactoryInterface
{
    public function createResolvedAddress(array $rawAddress, AbstractAddress $originalAddress): ?ResolvedAddress;
}
