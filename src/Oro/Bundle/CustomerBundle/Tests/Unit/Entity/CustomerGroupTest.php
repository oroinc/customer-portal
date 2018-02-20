<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Entity;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Component\Testing\Unit\EntityTestCase;

class CustomerGroupTest extends EntityTestCase
{
    /**
     * Test setters getters
     */
    public function testAccessors()
    {
        $this->assertPropertyAccessors($this->createCustomerGroupEntity(), [
            ['id', 42],
            ['name', 'Illuminatenorden'],
        ]);
    }

    /**
     * Test customers
     */
    public function testCustomerCollection()
    {
        $customerGroup = $this->createCustomerGroupEntity();

        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $customerGroup->getCustomers());
        $this->assertCount(0, $customerGroup->getCustomers());

        $customer = $this->createCustomerEntity();

        $this->assertInstanceOf(
            'Oro\Bundle\CustomerBundle\Entity\CustomerGroup',
            $customerGroup->addCustomer($customer)
        );

        $this->assertCount(1, $customerGroup->getCustomers());

        $customerGroup->addCustomer($customer);

        $this->assertCount(1, $customerGroup->getCustomers());

        $customerGroup->removeCustomer($customer);

        $this->assertCount(0, $customerGroup->getCustomers());
    }

    /**
     * @return CustomerGroup
     */
    protected function createCustomerGroupEntity()
    {
        return new CustomerGroup();
    }

    /**
     * @return Customer
     */
    protected function createCustomerEntity()
    {
        return new Customer();
    }
}
