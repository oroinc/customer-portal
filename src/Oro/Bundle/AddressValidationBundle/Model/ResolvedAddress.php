<?php

declare(strict_types=1);

namespace Oro\Bundle\AddressValidationBundle\Model;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;

/**
 * Represents a resolved address - a suggested candidate after the address validation.
 */
class ResolvedAddress extends AbstractAddress implements AddressValidatedAtAwareInterface
{
    use AddressValidatedAtAwareTrait;

    public function __construct(protected AbstractAddress $originalAddress)
    {
    }

    public function getOriginalAddress(): AbstractAddress
    {
        return $this->originalAddress;
    }

    public function setOriginalAddress(AbstractAddress $originalAddress): self
    {
        $this->originalAddress = $originalAddress;

        return $this;
    }
}
