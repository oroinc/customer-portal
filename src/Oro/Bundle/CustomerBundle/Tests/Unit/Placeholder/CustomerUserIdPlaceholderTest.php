<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Placeholder;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Placeholder\CustomerUserIdPlaceholder;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class CustomerUserIdPlaceholderTest extends \PHPUnit\Framework\TestCase
{
    /** @var CustomerUserIdPlaceholder */
    private $placeholder;

    /** @var TokenStorageInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $tokenStorage;

    protected function setUp()
    {
        $this->tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->placeholder = new CustomerUserIdPlaceholder($this->tokenStorage);
    }

    protected function tearDown()
    {
        unset($this->tokenStorage, $this->placeholder);
    }

    public function testGetPlaceholder()
    {
        $this->assertInternalType('string', $this->placeholder->getPlaceholder());
        $this->assertEquals('CUSTOMER_USER_ID', $this->placeholder->getPlaceholder());
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

    public function testGetValueWhenCustomerUserIsGiven()
    {
        $customerUserId = 7;
        $customerUser = $this->getMockBuilder(CustomerUser::class)
            ->getMock();

        $customerUser
            ->expects($this->once())
            ->method('getId')
            ->willReturn($customerUserId);

        $token = $this->createMock(TokenInterface::class);
        $token
            ->expects($this->any())
            ->method('getUser')
            ->willReturn($customerUser);

        $this->tokenStorage
            ->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->assertEquals($customerUserId, $this->placeholder->getDefaultValue());
    }

    public function testReplace()
    {
        $this->assertEquals(
            'visibility_customer_1',
            $this->placeholder->replace('visibility_customer_CUSTOMER_USER_ID', [CustomerUserIdPlaceholder::NAME => 1])
        );
    }
}
