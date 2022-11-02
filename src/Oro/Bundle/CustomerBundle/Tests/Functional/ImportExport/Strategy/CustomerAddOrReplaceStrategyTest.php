<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\ImportExport\Strategy;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\ImportExport\Strategy\CustomerAddOrReplaceStrategy;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomers;
use Oro\Bundle\CustomerBundle\Tests\Functional\ImportExport\Strategy\DataFixtures\LoadTestUser;
use Oro\Bundle\ImportExportBundle\Context\Context;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Component\Testing\ReflectionUtil;

/**
 * Checks the user for the possibility of using it as the owner of the entity
 */
class CustomerAddOrReplaceStrategyTest extends WebTestCase
{
    /** @var CustomerAddOrReplaceStrategy */
    private $strategy;

    /** @var Context */
    private $context;

    protected function setUp(): void
    {
        $this->initClient();
        $this->loadFixtures([LoadTestUser::class, LoadCustomers::class]);

        $this->createToken();
        $this->strategy = $this->getContainer()->get('oro_customer.importexport.strategy.customer.add_or_replace');

        /**
         * Strategy must know about entity name
         */
        $this->strategy->setEntityName(Customer::class);

        $this->context = new Context([]);
        $this->context->setValue('itemData', []);
        $this->strategy->setImportExportContext($this->context);
    }

    public function testWithValidOwner()
    {
        $customer = $this->createCustomer($this->getReference('user_with_main_organization_access'));

        $processedCustomer = $this->strategy->process($customer);
        $this->assertEquals([], $this->context->getErrors());
        $this->assertNotNull($processedCustomer);
        $this->assertInstanceOf(Customer::class, $processedCustomer);
    }

    public function testWithInvalidOwner()
    {
        $this->context->setValue('read_offset', 1);
        $customer = $this->createCustomer($this->getReference('user_without_main_organization_access'));

        $processedCustomer = $this->strategy->process($customer);
        $this->assertEquals(['Error in row #1. You have no access to set given owner'], $this->context->getErrors());
        $this->assertNull($processedCustomer);
    }

    public function testWithValidParent()
    {
        $parent = $this->getReference(LoadCustomers::CUSTOMER_LEVEL_1);
        $customer = $this->createCustomer($this->getReference('user_with_main_organization_access'), $parent);

        $processedCustomer = $this->strategy->process($customer);
        $this->assertEquals([], $this->context->getErrors());
        $this->assertNotNull($processedCustomer);
        $this->assertInstanceOf(Customer::class, $processedCustomer);
    }

    public function testWithNotFoundParentNotLastAttempt()
    {
        $context = new Context([
            'attempts' => 2,
            'max_attempts' => 3
        ]);
        $data = ['name' => 'customer', 'parent' => '0'];
        $context->setValue('itemData', $data);
        $context->setValue('rawItemData', $data);
        $this->strategy->setImportExportContext($context);

        $parent = new Customer();
        ReflectionUtil::setId($parent, 0);
        $customer = $this->createCustomer($this->getReference('user_with_main_organization_access'), $parent);

        $processedCustomer = $this->strategy->process($customer);
        $this->assertNull($processedCustomer);
        $this->assertEmpty($context->getErrors());
        $this->assertEquals([$data], $context->getPostponedRows());
    }

    public function testWithNotFoundParentLastAttempt()
    {
        $context = new Context([
            'attempts' => 3,
            'max_attempts' => 3
        ]);
        $data = ['name' => 'customer', 'parent' => '0'];
        $context->setValue('itemData', $data);
        $context->setValue('rawItemData', $data);
        $this->strategy->setImportExportContext($context);

        $parent = new Customer();
        ReflectionUtil::setId($parent, 0);
        $customer = $this->createCustomer($this->getReference('user_with_main_organization_access'), $parent);

        $processedCustomer = $this->strategy->process($customer);
        $this->assertNull($processedCustomer);
        $this->assertEquals(['Error in row #0. Parent customer with ID "0" was not found'], $context->getErrors());
        $this->assertEmpty($context->getPostponedRows());
    }

    /**
     * The strategy use user data to verify access to the entity modification
     */
    private function createToken(): void
    {
        $user = $this->getReference('user_with_main_organization_access');
        $this->updateUserSecurityToken($user->getEmail());
    }

    private function createCustomer(User $owner, Customer $parent = null): Customer
    {
        $customer = new Customer();
        $customer->setName('customer');
        $customer->setOwner($owner);
        $customer->setParent($parent);

        return $customer;
    }
}
