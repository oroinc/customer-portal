<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Config;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ConfigBundle\Config\ConfigBag;
use Oro\Bundle\ConfigBundle\Tests\Unit\Config\AbstractScopeManagerTestCase;
use Oro\Bundle\CustomerBundle\Config\CustomerGroupScopeManager;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Contracts\Cache\CacheInterface;

class CustomerGroupScopeManagerTest extends AbstractScopeManagerTestCase
{
    /** @var TokenStorageInterface|\PHPUnit\Framework\MockObject\MockObject */
    private TokenStorageInterface $tokenStorage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);

        $this->manager->setTokenStorage($this->tokenStorage);
    }

    public function testInitializeScopeId(): void
    {
        $token = $this->createMock(TokenInterface::class);

        $customer = $this->getEntity(Customer::class, [
            'group' => $this->getScopedEntity()
        ]);
        $token->expects($this->once())
            ->method('getUser')
            ->willReturn($this->getEntity(CustomerUser::class, ['id' => 123, 'customer' => $customer]));

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->assertEquals(456, $this->manager->getScopeId());
    }

    public function testInitializeScopeIdForUserWithEmptyCustomer(): void
    {
        $token = $this->createMock(TokenInterface::class);

        $token->expects($this->once())
            ->method('getUser')
            ->willReturn(new CustomerUser());

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->assertEquals(0, $this->manager->getScopeId());
    }

    public function testInitializeScopeIdForUserWithEmptyCustomerGroup(): void
    {
        $token = $this->createMock(TokenInterface::class);

        $customerUser = $this->getEntity(CustomerUser::class, [
            'customer' => new Customer()
        ]);

        $token->expects($this->once())
            ->method('getUser')
            ->willReturn($customerUser);

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->assertEquals(0, $this->manager->getScopeId());
    }

    public function testInitializeScopeIdForUserWithEmptyCustomerGroupIdentity(): void
    {
        $token = $this->createMock(TokenInterface::class);

        $customer = $this->getEntity(Customer::class, [
            'group' => new CustomerGroup()
        ]);
        $customerUser = $this->getEntity(CustomerUser::class, [
            'id' => 132,
            'customer' => $customer
        ]);

        $token->expects($this->once())
            ->method('getUser')
            ->willReturn($customerUser);

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->assertEquals(0, $this->manager->getScopeId());
    }

    public function testInitializeScopeIdForUnsupportedUserObject(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->once())
            ->method('getUser')
            ->willReturn(new Organization());

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->assertEquals(0, $this->manager->getScopeId());
    }

    public function testInitializeScopeIdNoToken(): void
    {
        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn(null);

        $this->assertEquals(0, $this->manager->getScopeId());
    }

    public function testSetScopeId(): void
    {
        $this->tokenStorage->expects($this->never())
            ->method('getToken');

        $this->manager->setScopeId(789);

        $this->assertEquals(789, $this->manager->getScopeId());
    }

    /**
     * {@inheritdoc}
     */
    protected function createManager(
        ManagerRegistry $doctrine,
        CacheInterface  $cache,
        EventDispatcher $eventDispatcher,
        ConfigBag       $configBag
    ): CustomerGroupScopeManager {
        return new CustomerGroupScopeManager(
            $doctrine,
            $cache,
            $eventDispatcher,
            $configBag,
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getScopedEntityName(): string
    {
        return 'customer_group';
    }

    /**
     * {@inheritdoc}
     */
    protected function getScopedEntity(): CustomerGroup
    {
        return $this->getEntity(CustomerGroup::class, ['id' => 456]);
    }
}
