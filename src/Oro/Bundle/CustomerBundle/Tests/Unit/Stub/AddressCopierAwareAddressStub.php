<?php

declare(strict_types=1);

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Stub;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;

/**
 * Address stub exposing the customer/customer-user address setters that AddressCopier links via method_exists().
 */
class AddressCopierAwareAddressStub extends AbstractAddress
{
    private ?CustomerAddress $customerAddress = null;

    private ?CustomerUserAddress $customerUserAddress = null;

    public function setCustomerAddress(?CustomerAddress $customerAddress): self
    {
        $this->customerAddress = $customerAddress;

        return $this;
    }

    public function getCustomerAddress(): ?CustomerAddress
    {
        return $this->customerAddress;
    }

    public function setCustomerUserAddress(?CustomerUserAddress $customerUserAddress): self
    {
        $this->customerUserAddress = $customerUserAddress;

        return $this;
    }

    public function getCustomerUserAddress(): ?CustomerUserAddress
    {
        return $this->customerUserAddress;
    }
}
