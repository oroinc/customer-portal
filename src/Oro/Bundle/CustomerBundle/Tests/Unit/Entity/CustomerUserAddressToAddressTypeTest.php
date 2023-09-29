<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Entity;

use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddressToAddressType;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;

class CustomerUserAddressToAddressTypeTest extends \PHPUnit\Framework\TestCase
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
