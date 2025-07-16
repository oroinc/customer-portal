<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Provider;

use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Provider\CustomerUserRelationsProvider;
use Oro\Bundle\CustomerBundle\Provider\ScopeCustomerGroupCriteriaProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ScopeCustomerGroupCriteriaProviderTest extends TestCase
{
    private TokenStorageInterface&MockObject $tokenStorage;
    private CustomerUserRelationsProvider&MockObject $customerUserRelationsProvider;
    private ScopeCustomerGroupCriteriaProvider $provider;

    #[\Override]
    protected function setUp(): void
    {
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->customerUserRelationsProvider = $this->createMock(CustomerUserRelationsProvider::class);

        $this->provider = new ScopeCustomerGroupCriteriaProvider(
            $this->tokenStorage,
            $this->customerUserRelationsProvider
        );
    }

    public function testGetCriteriaField(): void
    {
        $this->assertEquals(ScopeCustomerGroupCriteriaProvider::CUSTOMER_GROUP, $this->provider->getCriteriaField());
    }

    public function testGetCriteriaValue(): void
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

    public function testGetCriteriaValueWithoutToken(): void
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

    public function testGetCriteriaValueWithoutUser(): void
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

    public function testGetCriteriaValueType(): void
    {
        $this->assertEquals(CustomerGroup::class, $this->provider->getCriteriaValueType());
    }
}
