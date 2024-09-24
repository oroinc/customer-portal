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

    #[\Override]
    protected function setUp(): void
    {
        $this->configManager = $this->createMock(ConfigManager::class);
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);

        $this->provider = new CustomerUserRelationsProvider(
            $this->configManager,
            $this->doctrineHelper
        );
    }

    public function testGetCustomer(): void
    {
        $customer = new Customer();
        $customerUser = new CustomerUser();
        $customerUser->setCustomer($customer);

        self::assertSame($customer, $this->provider->getCustomer($customerUser));
    }

    public function testGetCustomerWhenNoCustomerUser(): void
    {
        self::assertNull($this->provider->getCustomer());
    }

    public function testGetCustomerGroup(): void
    {
        $customerGroup = new CustomerGroup();
        $customer = new Customer();
        $customer->setGroup($customerGroup);
        $customerUser = new CustomerUser();
        $customerUser->setCustomer($customer);

        self::assertSame($customerGroup, $this->provider->getCustomerGroup($customerUser));
    }

    public function testGetCustomerGroupWhenNoCustomer(): void
    {
        $customerUser = new CustomerUser();

        self::assertNull($this->provider->getCustomerGroup($customerUser));
    }

    public function testGetCustomerGroupWhenNoCustomerUser(): void
    {
        $customerGroup = new CustomerGroup();

        $this->configManager->expects(self::once())
            ->method('get')
            ->with('oro_customer.anonymous_customer_group')
            ->willReturn(10);
        $this->doctrineHelper->expects(self::once())
            ->method('getEntityReference')
            ->with(CustomerGroup::class, 10)
            ->willReturn($customerGroup);

        self::assertSame($customerGroup, $this->provider->getCustomerGroup());
    }

    public function testGetCustomerGroupWhenNoCustomerUserAndNoAnonymousCustomerGroup(): void
    {
        $this->configManager->expects(self::once())
            ->method('get')
            ->with('oro_customer.anonymous_customer_group')
            ->willReturn(null);
        $this->doctrineHelper->expects(self::never())
            ->method('getEntityReference');

        self::assertNull($this->provider->getCustomerGroup());
    }

    public function testGetCustomerIncludingEmpty(): void
    {
        $customerGroup = new CustomerGroup();
        $customer = new Customer();
        $customer->setGroup($customerGroup);
        $customerUser = new CustomerUser();
        $customerUser->setCustomer($customer);

        $this->configManager->expects(self::never())
            ->method('get');
        $this->doctrineHelper->expects(self::never())
            ->method('getEntityReference');

        self::assertSame($customer, $this->provider->getCustomerIncludingEmpty($customerUser));
    }

    public function testGetCustomerIncludingEmptyWhenNoCustomerUser(): void
    {
        $customerGroup = new CustomerGroup();
        $customer = new Customer();
        $customer->setGroup($customerGroup);

        $this->configManager->expects(self::once())
            ->method('get')
            ->with('oro_customer.anonymous_customer_group')
            ->willReturn(10);
        $this->doctrineHelper->expects(self::once())
            ->method('getEntityReference')
            ->with(CustomerGroup::class, 10)
            ->willReturn($customerGroup);

        $createdCustomer = $this->provider->getCustomerIncludingEmpty();
        self::assertEquals($customer, $createdCustomer);
        self::assertNotSame($customer, $createdCustomer);
    }

    public function testGetCustomerIncludingEmptyWhenNoCustomerUserAndNoAnonymousCustomerGroup(): void
    {
        $customerGroup = new CustomerGroup();
        $customer = new Customer();
        $customer->setGroup($customerGroup);

        $this->configManager->expects(self::once())
            ->method('get')
            ->with('oro_customer.anonymous_customer_group')
            ->willReturn(null);
        $this->doctrineHelper->expects(self::never())
            ->method('getEntityReference');

        self::assertEquals(new Customer(), $this->provider->getCustomerIncludingEmpty());
    }
}
