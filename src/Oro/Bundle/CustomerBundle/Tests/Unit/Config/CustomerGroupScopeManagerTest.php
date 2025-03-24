<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Config;

use Oro\Bundle\ConfigBundle\Tests\Unit\Config\AbstractScopeManagerTestCase;
use Oro\Bundle\CustomerBundle\Config\CustomerGroupScopeManager;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Component\Testing\ReflectionUtil;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class CustomerGroupScopeManagerTest extends AbstractScopeManagerTestCase
{
    private TokenStorageInterface&MockObject $tokenStorage;

    #[\Override]
    protected function setUp(): void
    {
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        parent::setUp();
    }

    #[\Override]
    protected function createManager(): CustomerGroupScopeManager
    {
        $manager = new CustomerGroupScopeManager($this->doctrine, $this->cache, $this->dispatcher, $this->configBag);
        $manager->setTokenStorage($this->tokenStorage);

        return $manager;
    }

    #[\Override]
    protected function getScopedEntityName(): string
    {
        return 'customer_group';
    }

    #[\Override]
    protected function getScopedEntity(): CustomerGroup
    {
        $entity = new CustomerGroup();
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
        $customer = new Customer();
        $customer->setGroup($this->getScopedEntity());

        $customerUser = $this->getCustomerUser();
        $customerUser->setCustomer($customer);

        $token = $this->createMock(TokenInterface::class);
        $token->expects(self::once())
            ->method('getUser')
            ->willReturn($customerUser);
        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn($token);

        self::assertSame(456, $this->manager->getScopeId());
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

    public function testInitializeScopeIdForUserWithEmptyCustomerGroup(): void
    {
        $customerUser = $this->getCustomerUser();
        $customerUser->setCustomer(new Customer());

        $token = $this->createMock(TokenInterface::class);
        $token->expects(self::once())
            ->method('getUser')
            ->willReturn($customerUser);
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

    public function testGetScopeIdFromEntityForCustomerGroupWithEmptyId(): void
    {
        self::assertSame(0, $this->manager->getScopeIdFromEntity(new CustomerGroup()));
    }

    public function testGetScopeIdFromEntityForCustomer(): void
    {
        $customerGroup = $this->getScopedEntity();
        $customer = new Customer();
        $customer->setGroup($customerGroup);

        self::assertSame($customerGroup->getId(), $this->manager->getScopeIdFromEntity($customer));
    }

    public function testGetScopeIdFromEntityForCustomerAssociatedToCustomerGroupWithEmptyId(): void
    {
        $customer = new Customer();
        $customer->setGroup(new CustomerGroup());

        self::assertSame(0, $this->manager->getScopeIdFromEntity($customer));
    }

    public function testGetScopeIdFromEntityForCustomerWithoutCustomerGroup(): void
    {
        self::assertNull($this->manager->getScopeIdFromEntity(new Customer()));
    }
}
