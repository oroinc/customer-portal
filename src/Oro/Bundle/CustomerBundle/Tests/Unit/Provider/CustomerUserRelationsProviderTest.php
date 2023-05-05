<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Provider;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Provider\CustomerUserRelationsProvider;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

class CustomerUserRelationsProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject */
    private $configManager;

    /** @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $doctrineHelper;

    /** @var CustomerUserRelationsProvider */
    private $provider;

    protected function setUp(): void
    {
        $this->configManager = $this->createMock(ConfigManager::class);
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);

        $this->provider = new CustomerUserRelationsProvider(
            $this->configManager,
            $this->doctrineHelper
        );
    }

    /**
     * @dataProvider customerDataProvider
     */
    public function testGetCustomer(CustomerUser $customerUser = null, Customer $expectedCustomer = null)
    {
        $this->assertEquals($expectedCustomer, $this->provider->getCustomer($customerUser));
    }

    public function customerDataProvider(): array
    {
        $customerUser = new CustomerUser();
        $customer = new Customer();
        $customerUser->setCustomer($customer);

        return [
            [null, null],
            [$customerUser, $customer]
        ];
    }

    /**
     * @dataProvider customerGroupDataProvider
     */
    public function testGetCustomerGroup(CustomerUser $customerUser = null, CustomerGroup $expectedCustomerGroup = null)
    {
        $this->assertEquals($expectedCustomerGroup, $this->provider->getCustomerGroup($customerUser));
    }

    public function customerGroupDataProvider(): array
    {
        $customerUser = new CustomerUser();
        $customer = new Customer();
        $customerGroup = new CustomerGroup();
        $customer->setGroup($customerGroup);
        $customerUser->setCustomer($customer);

        return [
            [null, null],
            [$customerUser, $customerGroup]
        ];
    }

    public function testGetCustomerGroupConfig()
    {
        $customerGroup = new CustomerGroup();
        $this->assertCustomerGroupConfigCall($customerGroup);

        $this->assertEquals($customerGroup, $this->provider->getCustomerGroup(null));
    }

    public function testGetCustomerIncludingEmptyAnonymous()
    {
        $customer = new Customer();
        $customerGroup = new CustomerGroup();
        $customerGroup->setName('test');
        $customer->setGroup($customerGroup);

        $this->assertCustomerGroupConfigCall($customerGroup);
        $this->assertEquals($customer, $this->provider->getCustomerIncludingEmpty(null));
    }

    public function testGetCustomerIncludingEmptyLogged()
    {
        $customer = new Customer();
        $customer->setName('test2');
        $customerGroup = new CustomerGroup();
        $customerGroup->setName('test2');
        $customer->setGroup($customerGroup);
        $customerUser = new CustomerUser();
        $customerUser->setCustomer($customer);

        $this->configManager->expects($this->never())
            ->method('get');

        $this->assertEquals($customer, $this->provider->getCustomerIncludingEmpty($customerUser));
    }

    protected function assertCustomerGroupConfigCall(CustomerGroup $customerGroup)
    {
        $this->configManager->expects($this->once())
            ->method('get')
            ->with('oro_customer.anonymous_customer_group')
            ->willReturn(10);
        $this->doctrineHelper->expects($this->once())
            ->method('getEntityReference')
            ->with('OroCustomerBundle:CustomerGroup', 10)
            ->willReturn($customerGroup);
    }
}
