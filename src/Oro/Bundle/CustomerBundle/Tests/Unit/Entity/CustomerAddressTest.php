<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Entity;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddressToAddressType;

class CustomerAddressTest extends AbstractAddressTest
{
    public function testProperties()
    {
        parent::testProperties();

        self::assertPropertyAccessors($this->address, [
            ['frontendOwner', new Customer()],
        ]);
    }

    /**
     * @return CustomerAddress
     */
    protected function createAddressEntity()
    {
        return new CustomerAddress();
    }

    /**
     * @return CustomerAddressToAddressType
     */
    protected function createAddressToTypeEntity()
    {
        return new CustomerAddressToAddressType();
    }
}
