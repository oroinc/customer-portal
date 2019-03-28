<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Provider;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Provider\ScopeCustomerCriteriaProvider;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ScopeCustomerCriteriaProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ScopeCustomerCriteriaProvider
     */
    private $provider;

    /**
     * @var TokenStorageInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $tokenStorage;

    protected function setUp()
    {
        $this->tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)->getMock();
        $this->provider = new ScopeCustomerCriteriaProvider($this->tokenStorage);
    }

    /**
     * @dataProvider currentScopeDataProvider
     * @param bool $hasToken
     * @param object|string|null $loggedUser
     * @param array $criteria
     */
    public function testGetCriteriaForCurrentScope($hasToken, $loggedUser, array $criteria)
    {
        $token = null;
        if ($hasToken) {
            $token = $this->createMock(TokenInterface::class);
            $token->expects($this->any())
                ->method('getUser')
                ->willReturn($loggedUser);
        }

        $this->tokenStorage->expects($this->any())
            ->method('getToken')
            ->willReturn($token);

        $actual = $this->provider->getCriteriaForCurrentScope();
        $this->assertEquals($criteria, $actual);
    }

    /**
     * @return array
     */
    public function currentScopeDataProvider()
    {
        $customerUser = new CustomerUser();
        $customer = new Customer();
        $customerUser->setCustomer($customer);

        return [
            'no token' => [false, null, ['customer' => null]],
            'no logged user' => [true, null, ['customer' => null]],
            'not supported logged user' => [true, new \stdClass(), ['customer' => null]],
            'supported logged user' => [true, $customerUser, ['customer' => $customer]]
        ];
    }

    public function testGetCriteriaForCurrentScopeNoToken()
    {
        $this->tokenStorage
            ->expects($this->once())
            ->method('getToken')
            ->willReturn(null);
        $actual = $this->provider->getCriteriaForCurrentScope();
        $this->assertEquals(['customer' => null], $actual);
    }

    /**
     * @dataProvider contextDataProvider
     *
     * @param mixed $context
     * @param array $criteria
     */
    public function testGetCriteria($context, array $criteria)
    {
        $actual = $this->provider->getCriteriaByContext($context);
        $this->assertEquals($criteria, $actual);
    }

    /**
     * @return array
     */
    public function contextDataProvider()
    {
        $customer = new Customer();
        $customerAware = new \stdClass();
        $customerAware->customer = $customer;

        return [
            'array_context_with_customer_key' => [
                'context' => ['customer' => $customer],
                'criteria' => ['customer' => $customer],
            ],
            'array_context_without_customer_key' => [
                'context' => [],
                'criteria' => [],
            ],
            'object_context_customer_aware' => [
                'context' => $customerAware,
                'criteria' => ['customer' => $customer],
            ],
            'object_context_not_customer_aware' => [
                'context' => new \stdClass(),
                'criteria' => [],
            ],
        ];
    }

    public function testGetCriteriaValueType()
    {
        $this->assertEquals(Customer::class, $this->provider->getCriteriaValueType());
    }
}
