<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Config;

use Oro\Bundle\ConfigBundle\Tests\Unit\Config\AbstractScopeManagerTestCase;
use Oro\Bundle\CustomerBundle\Config\CustomerScopeManager;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerAwareInterface;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Component\Testing\ReflectionUtil;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class CustomerScopeManagerTest extends AbstractScopeManagerTestCase
{
    /** @var TokenStorageInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $tokenStorage;

    protected function setUp(): void
    {
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        parent::setUp();
    }

    /**
     * {@inheritDoc}
     */
    protected function createManager(): CustomerScopeManager
    {
        $manager = new CustomerScopeManager($this->doctrine, $this->cache, $this->dispatcher, $this->configBag);
        $manager->setTokenStorage($this->tokenStorage);

        return $manager;
    }

    /**
     * {@inheritDoc}
     */
    protected function getScopedEntityName(): string
    {
        return 'customer';
    }

    /**
     * {@inheritDoc}
     */
    protected function getScopedEntity(): Customer
    {
        $entity = new Customer();
        ReflectionUtil::setId($entity, 456);

        return $entity;
    }

    private function getCustomerUser(): CustomerUser
    {
        $customerUser = new CustomerUser();
        ReflectionUtil::setId($customerUser, 123);

        return $customerUser;
    }

    public function testInitializeScopeId(): void
    {
        $customer = $this->getScopedEntity();

        $customerUser = $this->getCustomerUser();
        $customerUser->setCustomer($customer);

        $token = $this->createMock(TokenInterface::class);
        $token->expects(self::once())
            ->method('getUser')
            ->willReturn($customerUser);
        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn($token);

        self::assertSame($customer->getId(), $this->manager->getScopeId());
    }

    public function testInitializeScopeIdForUserWithEmptyCustomer(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $token->expects(self::once())
            ->method('getUser')
            ->willReturn($this->getCustomerUser());
        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn($token);

        self::assertSame(0, $this->manager->getScopeId());
    }

    public function testInitializeScopeIdForUnsupportedUserObject(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $token->expects(self::once())
            ->method('getUser')
            ->willReturn(new User());
        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn($token);

        self::assertSame(0, $this->manager->getScopeId());
    }

    public function testInitializeScopeIdNoToken(): void
    {
        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn(null);

        self::assertSame(0, $this->manager->getScopeId());
    }

    public function testGetScopeIdFromEntityForCustomerWithEmptyId(): void
    {
        self::assertSame(0, $this->manager->getScopeIdFromEntity(new Customer()));
    }

    public function testGetScopeIdFromEntityForCustomerAwareEntity(): void
    {
        $customer = $this->getScopedEntity();
        $entity = $this->createMock(CustomerAwareInterface::class);
        $entity->expects(self::exactly(2))
            ->method('getCustomer')
            ->willReturn($customer);

        self::assertSame($customer->getId(), $this->manager->getScopeIdFromEntity($entity));
    }

    public function testGetScopeIdFromEntityForCustomerAwareEntityAssociatedToCustomerWithEmptyId(): void
    {
        $entity = $this->createMock(CustomerAwareInterface::class);
        $entity->expects(self::exactly(2))
            ->method('getCustomer')
            ->willReturn(new Customer());

        self::assertSame(0, $this->manager->getScopeIdFromEntity($entity));
    }

    public function testGetScopeIdFromEntityForCustomerAwareEntityWithoutCustomer(): void
    {
        $entity = $this->createMock(CustomerAwareInterface::class);
        $entity->expects(self::once())
            ->method('getCustomer')
            ->willReturn(null);

        self::assertNull($this->manager->getScopeIdFromEntity($entity));
    }
}
