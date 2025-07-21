<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Placeholder;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Placeholder\CustomerIdPlaceholder;
use Oro\Bundle\UserBundle\Entity\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class CustomerIdPlaceholderTest extends TestCase
{
    private TokenStorageInterface&MockObject $tokenStorage;
    private CustomerIdPlaceholder $placeholder;

    #[\Override]
    protected function setUp(): void
    {
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);

        $this->placeholder = new CustomerIdPlaceholder($this->tokenStorage);
    }

    public function testGetPlaceholder(): void
    {
        $this->assertIsString($this->placeholder->getPlaceholder());
        $this->assertEquals('CUSTOMER_ID', $this->placeholder->getPlaceholder());
    }

    public function testGetValueWhenTokenIsNull(): void
    {
        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn(null);

        $this->assertNull($this->placeholder->getDefaultValue());
    }

    public function testGetValueWhenCustomerUserIsNotCustomerUser(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->once())
            ->method('getUser')
            ->willReturn(new User());

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->assertNull($this->placeholder->getDefaultValue());
    }

    public function testGetValueWithNoCustomer(): void
    {
        $customerUser = $this->createMock(CustomerUser::class);
        $customerUser->expects($this->once())
            ->method('getCustomer')
            ->willReturn(null);

        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->any())
            ->method('getUser')
            ->willReturn($customerUser);

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->assertNull($this->placeholder->getDefaultValue());
    }

    public function testGetValueWithValidCustomer(): void
    {
        $customerId = 5;

        $customer = $this->createMock(Customer::class);
        $customer->expects($this->once())
            ->method('getId')
            ->willReturn($customerId);

        $customerUser = $this->createMock(CustomerUser::class);
        $customerUser->expects($this->once())
            ->method('getCustomer')
            ->willReturn($customer);

        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->any())
            ->method('getUser')
            ->willReturn($customerUser);

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->assertEquals($customerId, $this->placeholder->getDefaultValue());
    }

    public function testReplace(): void
    {
        $this->assertEquals(
            'test_field_1',
            $this->placeholder->replace('test_field_CUSTOMER_ID', [CustomerIdPlaceholder::NAME => 1])
        );
    }
}
