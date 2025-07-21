<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Placeholder;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;
use Oro\Bundle\CustomerBundle\Placeholder\CustomerUserIdPlaceholder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class CustomerUserIdPlaceholderTest extends TestCase
{
    private TokenStorageInterface&MockObject $tokenStorage;
    private CustomerUserIdPlaceholder $placeholder;

    #[\Override]
    protected function setUp(): void
    {
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);

        $this->placeholder = new CustomerUserIdPlaceholder($this->tokenStorage);
    }

    public function testGetPlaceholder(): void
    {
        $this->assertIsString($this->placeholder->getPlaceholder());
        $this->assertEquals('CUSTOMER_USER_ID', $this->placeholder->getPlaceholder());
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
            ->willReturn($this->createMock(CustomerVisitor::class));

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->assertNull($this->placeholder->getDefaultValue());
    }

    public function testGetValueWhenCustomerUserIsGiven(): void
    {
        $customerUserId = 7;
        $customerUser = $this->createMock(CustomerUser::class);

        $customerUser->expects($this->once())
            ->method('getId')
            ->willReturn($customerUserId);

        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->any())
            ->method('getUser')
            ->willReturn($customerUser);

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->assertEquals($customerUserId, $this->placeholder->getDefaultValue());
    }

    public function testReplace(): void
    {
        $this->assertEquals(
            'visibility_customer_1',
            $this->placeholder->replace('visibility_customer_CUSTOMER_USER_ID', [CustomerUserIdPlaceholder::NAME => 1])
        );
    }
}
