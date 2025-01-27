<?php

declare(strict_types=1);

namespace Oro\Bundle\CustomerBundle\Utils;

use Oro\Bundle\CustomerBundle\Entity\AddressBookAwareInterface;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;

/**
 * Contains handy methods to work with address book aware addresses, i.e. {@see CustomerAddress}
 * and {@see CustomerUserAddress}.
 */
class AddressBookAddressUtils
{
    public static function getAddressBookAddress(
        AddressBookAwareInterface $address
    ): CustomerAddress|CustomerUserAddress|null {
        $addressBookAddress = $address->getCustomerAddress();

        if (!$addressBookAddress) {
            $addressBookAddress = $address->getCustomerUserAddress();
        }

        return $addressBookAddress;
    }

    public static function setAddressBookAddress(
        AddressBookAwareInterface $address,
        CustomerAddress|CustomerUserAddress $addressBookAddress
    ): void {
        if ($addressBookAddress instanceof CustomerAddress) {
            $address->setCustomerAddress($addressBookAddress);
        } else {
            $address->setCustomerUserAddress($addressBookAddress);
        }
    }

    public static function resetAddressBookAddress(AddressBookAwareInterface $address): void
    {
        $address->setCustomerAddress(null);
        $address->setCustomerUserAddress(null);
    }

    public static function setFrontendOwner(
        CustomerAddress|CustomerUserAddress $address,
        Customer|CustomerUser|null $frontendOwner = null,
    ): void {
        if ($address instanceof CustomerAddress) {
            if ($frontendOwner instanceof CustomerUser) {
                $frontendOwner = $frontendOwner->getCustomer();
            }

            $address->setFrontendOwner($frontendOwner);
        }

        if ($address instanceof CustomerUserAddress) {
            if ($frontendOwner instanceof Customer) {
                throw new \InvalidArgumentException(sprintf(
                    'Invalid type for "frontendOwner": expected %s or null for the CustomerUserAddress, got "%s".',
                    CustomerUser::class,
                    Customer::class
                ));
            }

            $address->setFrontendOwner($frontendOwner);
        }
    }
}
