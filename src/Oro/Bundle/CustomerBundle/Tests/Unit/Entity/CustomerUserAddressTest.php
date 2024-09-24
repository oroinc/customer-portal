<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Entity;

use Oro\Bundle\CustomerBundle\Entity\AbstractDefaultTypedAddress;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;

class CustomerUserAddressTest extends AbstractAddressTest
{
    #[\Override]
    protected function createAddressEntity(): AbstractDefaultTypedAddress
    {
        return new CustomerUserAddress();
    }

    public function testFrontendOwner(): void
    {
        $customerUser = new CustomerUser();
        $address = new CustomerUserAddress();

        $address->setFrontendOwner($customerUser);
        self::assertSame($customerUser, $address->getFrontendOwner());
        self::assertCount(1, $customerUser->getAddresses());
        self::assertSame($address, $customerUser->getAddresses()->first());

        $address->setFrontendOwner($customerUser);
        self::assertSame($customerUser, $address->getFrontendOwner());
        self::assertCount(1, $customerUser->getAddresses());
        self::assertSame($address, $customerUser->getAddresses()->first());

        $address->setFrontendOwner(null);
        self::assertNull($address->getFrontendOwner());
        self::assertCount(0, $customerUser->getAddresses());
    }
}
