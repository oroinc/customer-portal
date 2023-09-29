<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Entity;

use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddressToAddressType;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;

class CustomerAddressToAddressTypeTest extends \PHPUnit\Framework\TestCase
{
    use EntityTestCaseTrait;

    public function testProperties(): void
    {
        $addressToAddressType = new CustomerAddressToAddressType();
        $this->assertPropertyAccessors($addressToAddressType, [
            ['id', 1],
            ['address', new CustomerAddress()],
            ['type', new AddressType(AddressType::TYPE_BILLING)],
            ['default', true],
        ]);
    }
}
