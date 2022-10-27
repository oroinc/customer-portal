<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Entity;

use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddressToAddressType;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;

class CustomerAddressToAddressTypeTest extends \PHPUnit\Framework\TestCase
{
    use EntityTestCaseTrait;

    /**
     * Test setters getters
     */
    public function testAccessors()
    {
        $this->assertPropertyAccessors($this->createAddressToAddressTypeEntity(), [
            ['id', 1],
            ['address', $this->createAddressEntity()],
            ['type', $this->createAddressTypeEntity(AddressType::TYPE_BILLING)],
            ['default', true],
        ]);
    }

    /**
     * @return CustomerAddressToAddressType
     */
    protected function createAddressToAddressTypeEntity()
    {
        return new CustomerAddressToAddressType();
    }

    /**
     * @return CustomerAddress
     */
    protected function createAddressEntity()
    {
        return new CustomerAddress();
    }

    /**
     * @param string $name
     * @return AddressType
     */
    protected function createAddressTypeEntity($name)
    {
        return new AddressType($name);
    }
}
