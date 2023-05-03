<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Config;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ConfigBundle\Config\ConfigBag;
use Oro\Bundle\ConfigBundle\Tests\Unit\Config\AbstractScopeManagerTestCase;
use Oro\Bundle\CustomerBundle\Config\CustomerScopeManager;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Contracts\Cache\CacheInterface;

class CustomerScopeManagerTest extends AbstractScopeManagerTestCase
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

        $token->expects($this->once())
            ->method('getUser')
            ->willReturn($this->createCustomerUser(['id' => 123, 'customer' => $this->getScopedEntity()]));

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

    public function testInitializeScopeIdForUserWithEmptyCustomerIdentity(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $customerUser = $this->createCustomerUser([
            'id' => 132,
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

    public function testInitializeScopeIdForUnsupportedUserObject(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->once())
            ->method('getUser')
            ->willReturn(new User());

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
        CacheInterface $cache,
        EventDispatcher $eventDispatcher,
        ConfigBag $configBag
    ): CustomerScopeManager {
        return new CustomerScopeManager(
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
        return 'customer';
    }

    /**
     * {@inheritdoc}
     */
    protected function getScopedEntity(): Customer
    {
        return $this->getEntity(Customer::class, ['id' => 456]);
    }

    private function createCustomerUser(array $parameters): CustomerUser
    {
        return $this->getEntity(CustomerUser::class, $parameters);
    }
}
