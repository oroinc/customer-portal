<?php

declare(strict_types=1);

namespace Oro\Bundle\AddressValidationBundle\ResolvedAddress;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\AddressValidationBundle\Model\ResolvedAddress;

/**
 * Accepts the resolved address by copying it to the original address.
 */
interface ResolvedAddressAcceptorInterface
{
    public function acceptResolvedAddress(ResolvedAddress $resolvedAddress): AbstractAddress;
}
