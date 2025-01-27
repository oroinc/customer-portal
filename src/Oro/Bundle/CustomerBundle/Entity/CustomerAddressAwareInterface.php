<?php

declare(strict_types=1);

namespace Oro\Bundle\CustomerBundle\Entity;

/**
 * Interface for the address model aware of {@see CustomerAddress};
 */
interface CustomerAddressAwareInterface
{
    public function setCustomerAddress(?CustomerAddress $customerAddress = null): self;

    public function getCustomerAddress(): ?CustomerAddress;
}
