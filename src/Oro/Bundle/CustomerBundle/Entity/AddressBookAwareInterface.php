<?php

namespace Oro\Bundle\CustomerBundle\Entity;

/**
 * Interface for the address model aware of {@see CustomerAddress} and {@see CustomerUserAddress};
 */
interface AddressBookAwareInterface extends CustomerAddressAwareInterface, CustomerUserAddressAwareInterface
{
}
