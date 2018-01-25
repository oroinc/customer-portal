<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Provider;

use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Provider\CustomerUserRelationsProvider;
use Oro\Bundle\CustomerBundle\Provider\ScopeCustomerGroupCriteriaProvider;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ScopeCustomerGroupCriteriaProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TokenStorageInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $tokenStorage;

    /**
     * @var CustomerUserRelationsProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerUserRelationsProvider;

    /**
     * @var ScopeCustomerGroupCriteriaProvider
     */
    protected $scopeCustomerGroupCriteriaProvider;

    protected function setUp()
    {
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->customerUserRelationsProvider = $this->createMock(CustomerUserRelationsProvider::class);
        $this->scopeCustomerGroupCriteriaProvider = new ScopeCustomerGroupCriteriaProvider(
            $this->tokenStorage,
            $this->customerUserRelationsProvider
        );
    }

    public function testGetCriteriaField()
    {
        $this->assertEquals('customerGroup', $this->scopeCustomerGroupCriteriaProvider->getCriteriaField());
    }

    public function testGetCriteriaForCurrentScopeWhenNoToken()
    {
        $this->tokenStorage
            ->expects($this->once())
            ->method('getToken')
            ->willReturn(null);

        $anonymousGroup = new CustomerGroup();
        $this->customerUserRelationsProvider
            ->expects($this->once())
            ->method('getCustomerGroup')
            ->with(null)
            ->willReturn($anonymousGroup);

        $expectedCriteria = ['customerGroup' => $anonymousGroup];
        $this->assertEquals($expectedCriteria, $this->scopeCustomerGroupCriteriaProvider->getCriteriaForCurrentScope());
    }

    public function testGetCriteriaForCurrentScopeWhenNoCustomerUser()
    {
        $token = $this->createMock(TokenInterface::class);
        $token
            ->expects($this->any())
            ->method('getUser')
            ->willReturn(null);

        $this->tokenStorage
            ->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $anonymousGroup = new CustomerGroup();
        $this->customerUserRelationsProvider
            ->expects($this->once())
            ->method('getCustomerGroup')
            ->with(null)
            ->willReturn($anonymousGroup);

        $expectedCriteria = ['customerGroup' => $anonymousGroup];
        $this->assertEquals($expectedCriteria, $this->scopeCustomerGroupCriteriaProvider->getCriteriaForCurrentScope());
    }

    public function testGetCriteriaForCurrentScopeWhenCustomerUser()
    {
        $customerUser = new CustomerUser();
        $token = $this->createMock(TokenInterface::class);
        $token
            ->expects($this->any())
            ->method('getUser')
            ->willReturn($customerUser);

        $this->tokenStorage
            ->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $customerUserGroup = new CustomerGroup();
        $this->customerUserRelationsProvider
            ->expects($this->once())
            ->method('getCustomerGroup')
            ->with($customerUser)
            ->willReturn($customerUserGroup);

        $expectedCriteria = ['customerGroup' => $customerUserGroup];
        $this->assertEquals($expectedCriteria, $this->scopeCustomerGroupCriteriaProvider->getCriteriaForCurrentScope());
    }

    public function testGetCriteriaValueType()
    {
        $this->assertEquals(CustomerGroup::class, $this->scopeCustomerGroupCriteriaProvider->getCriteriaValueType());
    }
}
