<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Entity;

use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddressToAddressType;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;
use PHPUnit\Framework\TestCase;

class CustomerUserAddressToAddressTypeTest extends TestCase
{
    use EntityTestCaseTrait;

    public function testProperties(): void
    {
        $addressToAddressType = new CustomerUserAddressToAddressType();
        $this->assertPropertyAccessors($addressToAddressType, [
            ['id', 1],
            ['address', new CustomerUserAddress()],
            ['type', new AddressType(AddressType::TYPE_BILLING)],
            ['default', true],
        ]);
    }
}
