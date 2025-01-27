<?php

namespace Oro\Bundle\AddressValidationBundle\Resolver;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\AddressValidationBundle\Model\ResolvedAddress;

/**
 * Interface for the address validation resolver that validates the specified address and returns suggested addresses.
 */
interface AddressValidationResolverInterface
{
    /**
     * @return array<int,ResolvedAddress> List of suggested addresses.
     */
    public function resolve(AbstractAddress $address): array;
}
