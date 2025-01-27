<?php

declare(strict_types=1);

namespace Oro\Bundle\AddressValidationBundle\Model;

/**
 * Interface for address entities aware of validatedAt field.
 */
interface AddressValidatedAtAwareInterface
{
    public function getValidatedAt(): ?\DateTimeInterface;

    public function setValidatedAt(?\DateTimeInterface $validatedAt): self;
}
