<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Entity;

use Oro\Bundle\CustomerBundle\Entity\AbstractDefaultTypedAddress;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Component\Testing\ReflectionUtil;

class CustomerAddressTest extends AbstractAddressTest
{
    /**
     * {@inheritDoc}
     */
    protected function createAddressEntity(): AbstractDefaultTypedAddress
    {
        return new CustomerAddress();
    }

    public function testFrontendOwner(): void
    {
        $customer = new Customer();
        $address = new CustomerAddress();

        $address->setFrontendOwner($customer);
        self::assertSame($customer, $address->getFrontendOwner());
        self::assertCount(1, $customer->getAddresses());
        self::assertSame($address, $customer->getAddresses()->first());

        $address->setFrontendOwner($customer);
        self::assertSame($customer, $address->getFrontendOwner());
        self::assertCount(1, $customer->getAddresses());
        self::assertSame($address, $customer->getAddresses()->first());

        $address->setFrontendOwner(null);
        self::assertNull($address->getFrontendOwner());
        self::assertCount(0, $customer->getAddresses());
    }

    public function testFrontendOwnerWithExistingAddress(): void
    {
        $customer = new Customer();
        $address = new CustomerAddress();
        ReflectionUtil::setId($address, 123);
        $address->setFrontendOwner($customer);
        self::assertSame($customer, $address->getFrontendOwner());
        self::assertCount(0, $customer->getAddresses());
    }
}
