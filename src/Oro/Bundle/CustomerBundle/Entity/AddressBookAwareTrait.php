<?php

namespace Oro\Bundle\CustomerBundle\Entity;

/**
 * Trait for the address model aware of {@see CustomerAddress} and {@see CustomerUserAddress};
 */
trait AddressBookAwareTrait
{
    use CustomerAddressAwareTrait;
    use CustomerUserAddressAwareTrait;
}
