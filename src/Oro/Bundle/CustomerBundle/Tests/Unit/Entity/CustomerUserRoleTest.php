<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Entity;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;

class CustomerUserRoleTest extends \PHPUnit_Framework_TestCase
{
    use EntityTestCaseTrait;

    public function testRole()
    {
        $name = 'test role#$%';
        $role = new CustomerUserRole();
        $customer = new Customer();
        $organization = new Organization();

        $this->assertEmpty($role->getId());
        $this->assertEmpty($role->getLabel());
        $this->assertEmpty($role->getRole());
        $this->assertEmpty($role->getOrganization());
        $this->assertEmpty($role->getCustomer());

        $role->setCustomer($customer);
        $role->setOrganization($organization);

        $this->assertEquals($organization, $role->getOrganization());
        $this->assertEquals($customer, $role->getCustomer());

        $role->setLabel($name);
        $this->assertEquals($name, $role->getLabel());

        $this->assertEquals(CustomerUserRole::PREFIX_ROLE, $role->getPrefix());

        $role->setRole($name);
        $this->assertStringStartsWith(CustomerUserRole::PREFIX_ROLE . 'TEST_ROLE_', $role->getRole());

        $this->assertEquals($name, (string)$role);
    }

    /**
     * Test CustomerUserRole relations
     */
    public function testRelations()
    {
        static::assertPropertyCollections(new CustomerUserRole(), [
            ['websites', new Website()],
            ['customerUsers', new CustomerUser()],
        ]);

        static::assertPropertyAccessors(new CustomerUserRole(), [
            ['customer', new Customer()],
            ['organization', new Organization()]
        ]);
    }

    public function testNotEmptyRole()
    {
        $name = 'another test role';
        $role = new CustomerUserRole($name);
        $this->assertEquals(CustomerUserRole::PREFIX_ROLE . 'ANOTHER_TEST_ROLE', $role->getRole());
    }


    public function testSelfManaged()
    {
        $role = new CustomerUserRole('test');

        $this->assertFalse($role->isSelfManaged());

        $role->setSelfManaged(true);

        $this->assertTrue($role->isSelfManaged());
    }

    public function testIsPredefined()
    {
        $name = 'Predefined role';

        $role = new CustomerUserRole($name);
        $this->assertTrue($role->isPredefined());

        $role->setCustomer(new Customer());
        $this->assertFalse($role->isPredefined());
    }
}
