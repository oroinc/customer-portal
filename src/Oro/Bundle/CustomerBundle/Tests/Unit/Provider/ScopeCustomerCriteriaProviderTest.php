<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Provider;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Provider\ScopeCustomerCriteriaProvider;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ScopeCustomerCriteriaProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var TokenStorageInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $tokenStorage;

    /** @var ScopeCustomerCriteriaProvider */
    private $provider;

    protected function setUp(): void
    {
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);

        $this->provider = new ScopeCustomerCriteriaProvider($this->tokenStorage);
    }

    public function testGetCriteriaField()
    {
        $this->assertEquals(ScopeCustomerCriteriaProvider::CUSTOMER, $this->provider->getCriteriaField());
    }

    public function testGetCriteriaValue()
    {
        $customer = new Customer();
        $customerUser = new CustomerUser();
        $customerUser->setCustomer($customer);

        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->once())
            ->method('getUser')
            ->willReturn($customerUser);

        $this->tokenStorage->expects($this->any())
            ->method('getToken')
            ->willReturn($token);

        $this->assertSame($customer, $this->provider->getCriteriaValue());
    }

    public function testGetCriteriaValueForNotSupportedUser()
    {
        $user = new User();

        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $this->tokenStorage->expects($this->any())
            ->method('getToken')
            ->willReturn($token);

        $this->assertNull($this->provider->getCriteriaValue());
    }

    public function testGetCriteriaValueWithoutToken()
    {
        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn(null);

        $this->assertNull($this->provider->getCriteriaValue());
    }

    public function testGetCriteriaValueWithoutUser()
    {
        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->once())
            ->method('getUser')
            ->willReturn(null);

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->assertNull($this->provider->getCriteriaValue());
    }

    public function testGetCriteriaValueType()
    {
        $this->assertEquals(Customer::class, $this->provider->getCriteriaValueType());
    }
}
