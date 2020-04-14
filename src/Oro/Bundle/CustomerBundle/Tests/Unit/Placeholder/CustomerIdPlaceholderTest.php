<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Placeholder;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Placeholder\CustomerIdPlaceholder;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class CustomerIdPlaceholderTest extends \PHPUnit\Framework\TestCase
{
    /** @var CustomerIdPlaceholder */
    private $placeholder;

    /** @var TokenStorageInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $tokenStorage;

    protected function setUp(): void
    {
        $this->tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->placeholder = new CustomerIdPlaceholder($this->tokenStorage);
    }

    protected function tearDown(): void
    {
        unset($this->tokenStorage, $this->placeholder);
    }

    public function testGetPlaceholder()
    {
        $this->assertIsString($this->placeholder->getPlaceholder());
        $this->assertEquals('CUSTOMER_ID', $this->placeholder->getPlaceholder());
    }

    public function testGetValueWhenTokenIsNull()
    {
        $this->tokenStorage
            ->expects($this->once())
            ->method('getToken')
            ->willReturn(null);

        $this->assertNull($this->placeholder->getDefaultValue());
    }

    public function testGetValueWhenCustomerUserIsNotCustomerUser()
    {
        $token = $this->createMock(TokenInterface::class);
        $token
            ->expects($this->once())
            ->method('getUser')
            ->willReturn('Anonymous');

        $this->tokenStorage
            ->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->assertNull($this->placeholder->getDefaultValue());
    }

    public function testGetValueWithNoCustomer()
    {
        $customerUser = $this->getMockBuilder(CustomerUser::class)
            ->getMock();
        $customerUser
            ->expects($this->once())
            ->method('getCustomer')
            ->willReturn(null);

        $token = $this->createMock(TokenInterface::class);
        $token
            ->expects($this->any())
            ->method('getUser')
            ->willReturn($customerUser);

        $this->tokenStorage
            ->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->assertNull($this->placeholder->getDefaultValue());
    }

    public function testGetValueWithValidCustomer()
    {
        $customerId = 5;

        $customer = $this->getMockBuilder(Customer::class)
            ->getMock();
        $customer
            ->expects($this->once())
            ->method('getId')
            ->willReturn($customerId);

        $customerUser = $this->getMockBuilder(CustomerUser::class)
            ->getMock();
        $customerUser
            ->expects($this->once())
            ->method('getCustomer')
            ->willReturn($customer);

        $token = $this->createMock(TokenInterface::class);
        $token
            ->expects($this->any())
            ->method('getUser')
            ->willReturn($customerUser);

        $this->tokenStorage
            ->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->assertEquals($customerId, $this->placeholder->getDefaultValue());
    }

    public function testReplace()
    {
        $this->assertEquals(
            'test_field_1',
            $this->placeholder->replace('test_field_CUSTOMER_ID', [CustomerIdPlaceholder::NAME => 1])
        );
    }
}
