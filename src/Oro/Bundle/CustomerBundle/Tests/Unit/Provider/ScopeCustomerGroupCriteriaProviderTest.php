<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Provider;

use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Provider\CustomerUserRelationsProvider;
use Oro\Bundle\CustomerBundle\Provider\ScopeCustomerGroupCriteriaProvider;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ScopeCustomerGroupCriteriaProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var TokenStorageInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $tokenStorage;

    /** @var CustomerUserRelationsProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $customerUserRelationsProvider;

    /** @var ScopeCustomerGroupCriteriaProvider */
    private $provider;

    protected function setUp(): void
    {
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->customerUserRelationsProvider = $this->createMock(CustomerUserRelationsProvider::class);

        $this->provider = new ScopeCustomerGroupCriteriaProvider(
            $this->tokenStorage,
            $this->customerUserRelationsProvider
        );
    }

    public function testGetCriteriaField()
    {
        $this->assertEquals(ScopeCustomerGroupCriteriaProvider::CUSTOMER_GROUP, $this->provider->getCriteriaField());
    }

    public function testGetCriteriaValue()
    {
        $customerUser = new CustomerUser();

        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->any())
            ->method('getUser')
            ->willReturn($customerUser);

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $customerUserGroup = new CustomerGroup();
        $this->customerUserRelationsProvider->expects($this->once())
            ->method('getCustomerGroup')
            ->with($this->identicalTo($customerUser))
            ->willReturn($customerUserGroup);

        $this->assertSame($customerUserGroup, $this->provider->getCriteriaValue());
    }

    public function testGetCriteriaValueWithoutToken()
    {
        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn(null);

        $anonymousGroup = new CustomerGroup();
        $this->customerUserRelationsProvider->expects($this->once())
            ->method('getCustomerGroup')
            ->with(null)
            ->willReturn($anonymousGroup);

        $this->assertSame($anonymousGroup, $this->provider->getCriteriaValue());
    }

    public function testGetCriteriaValueWithoutUser()
    {
        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->any())
            ->method('getUser')
            ->willReturn(null);

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $anonymousGroup = new CustomerGroup();
        $this->customerUserRelationsProvider->expects($this->once())
            ->method('getCustomerGroup')
            ->with(null)
            ->willReturn($anonymousGroup);

        $this->assertSame($anonymousGroup, $this->provider->getCriteriaValue());
    }

    public function testGetCriteriaValueType()
    {
        $this->assertEquals(CustomerGroup::class, $this->provider->getCriteriaValueType());
    }
}
