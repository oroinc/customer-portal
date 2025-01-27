<?php

declare(strict_types=1);

namespace Oro\Bundle\CustomerBundle\Entity;

/**
 * Interface for the address model aware of {@see CustomerUserAddress};
 */
interface CustomerUserAddressAwareInterface
{
    public function setCustomerUserAddress(?CustomerUserAddress $customerUserAddress = null): self;

    public function getCustomerUserAddress(): ?CustomerUserAddress;
}
