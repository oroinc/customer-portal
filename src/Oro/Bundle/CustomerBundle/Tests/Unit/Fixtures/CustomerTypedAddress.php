<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Fixtures;

use Oro\Bundle\AddressBundle\Tests\Unit\Fixtures\TypedAddressOwner;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;

class CustomerTypedAddress extends CustomerAddress
{
    protected TypedAddressOwner $frontendOwner;

    /**
     * @return TypedAddressOwner
     */
    #[\Override]
    public function getFrontendOwner()
    {
        return $this->frontendOwner;
    }

    /**
     * @param Customer $frontendOwner
     * @return CustomerTypedAddress
     */
    #[\Override]
    public function setFrontendOwner($frontendOwner = null)
    {
        $this->frontendOwner = $frontendOwner;

        return $this;
    }
}
