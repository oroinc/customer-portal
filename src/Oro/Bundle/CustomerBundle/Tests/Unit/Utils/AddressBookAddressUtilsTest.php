<?php

declare(strict_types=1);

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Utils;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;
use Oro\Bundle\CustomerBundle\Tests\Unit\Stub\AddressBookAwareAddressStub;
use Oro\Bundle\CustomerBundle\Utils\AddressBookAddressUtils;
use PHPUnit\Framework\TestCase;

final class AddressBookAddressUtilsTest extends TestCase
{
    public function testGetAddressBookAddressWithCustomerAddressAwareInterface(): void
    {
        $customerAddress = new CustomerAddress();
        $address = (new AddressBookAwareAddressStub())
            ->setCustomerAddress($customerAddress);

        $result = AddressBookAddressUtils::getAddressBookAddress($address);

        self::assertSame($customerAddress, $result);
    }

    public function testGetAddressBookAddressWithCustomerUserAddressAwareInterface(): void
    {
        $customerUserAddress = new CustomerUserAddress();
        $address = (new AddressBookAwareAddressStub())
            ->setCustomerUserAddress($customerUserAddress);

        $result = AddressBookAddressUtils::getAddressBookAddress($address);

        self::assertSame($customerUserAddress, $result);
    }

    public function testSetAddressBookAddressForCustomerAddress(): void
    {
        $customerAddress = new CustomerAddress();
        $address = new AddressBookAwareAddressStub();

        AddressBookAddressUtils::setAddressBookAddress($address, $customerAddress);

        self::assertSame($customerAddress, $address->getCustomerAddress());
    }

    public function testSetAddressBookAddressForCustomerUserAddress(): void
    {
        $customerUserAddress = new CustomerUserAddress();
        $address = new AddressBookAwareAddressStub();

        AddressBookAddressUtils::setAddressBookAddress($address, $customerUserAddress);

        self::assertSame($customerUserAddress, $address->getCustomerUserAddress());
    }

    public function testResetAddressBookAddress(): void
    {
        $customerAddress = new CustomerAddress();
        $customerUserAddress = new CustomerUserAddress();
        $address = (new AddressBookAwareAddressStub())
            ->setCustomerAddress($customerAddress)
            ->setCustomerUserAddress($customerUserAddress);

        AddressBookAddressUtils::resetAddressBookAddress($address);

        self::assertNull($address->getCustomerAddress());
        self::assertNull($address->getCustomerUserAddress());
    }

    public function testSetFrontendOwnerForCustomerAddress(): void
    {
        $customerAddress = new CustomerAddress();
        $customer = new Customer();

        AddressBookAddressUtils::setFrontendOwner($customerAddress, $customer);

        self::assertSame($customer, $customerAddress->getFrontendOwner());
    }

    public function testSetFrontendOwnerForCustomerUserAddress(): void
    {
        $customerUserAddress = new CustomerUserAddress();
        $customerUser = new CustomerUser();

        AddressBookAddressUtils::setFrontendOwner($customerUserAddress, $customerUser);

        self::assertSame($customerUser, $customerUserAddress->getFrontendOwner());
    }

    public function testSetCustomerFrontendOwnerForCustomerAddress(): void
    {
        $customerAddress = new CustomerAddress();
        $customer = new Customer();
        $customerUser = (new CustomerUser())
            ->setCustomer($customer);

        AddressBookAddressUtils::setFrontendOwner($customerAddress, $customerUser);

        self::assertSame($customer, $customerAddress->getFrontendOwner());
    }

    public function testSetCustomerUserFrontendOwnerWillThrowExceptionWhenCustomerProvided(): void
    {
        $customerUserAddress = new CustomerUserAddress();
        $customer = new Customer();

        self::expectException(\InvalidArgumentException::class);

        AddressBookAddressUtils::setFrontendOwner($customerUserAddress, $customer);
    }
}
