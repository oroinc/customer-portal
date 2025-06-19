<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\DependencyInjection\Configuration;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\CustomerBundle\EventListener\OrganizationCustomerGroupListener;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Component\Testing\ReflectionUtil;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class OrganizationCustomerGroupListenerTest extends TestCase
{
    private TokenAccessorInterface&MockObject $tokenAccessor;
    private ConfigManager|MockObject $configManager;
    private OrganizationCustomerGroupListener $listener;

    protected function setUp(): void
    {
        $this->tokenAccessor = self::createMock(TokenAccessorInterface::class);
        $this->configManager = self::createMock(ConfigManager::class);
        $this->listener = new OrganizationCustomerGroupListener($this->tokenAccessor, $this->configManager);
    }

    public function testPrePersist(): void
    {
        $user = new User();
        $token = self::createMock(TokenInterface::class);
        $token->expects(self::once())
            ->method('getUser')
            ->willReturn($user);

        $this->tokenAccessor->expects(self::once())
            ->method('getToken')
            ->willReturn($token);

        $organization = new Organization();
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())
            ->method('persist')
            ->with(self::callback(function (CustomerGroup $customerUser) use ($organization, $user): bool {
                self::assertEquals('Non-Authenticated Visitors', $customerUser->getName());
                self::assertEquals($organization, $customerUser->getOrganization());
                self::assertEquals($user, $customerUser->getOwner());

                return true;
            }));

        $event = new PrePersistEventArgs($organization, $entityManager);

        $this->listener->prePersist($organization, $event);
    }

    public function testPrePersistWithOrganizationId(): void
    {
        $organization = new Organization();
        $organization->setId(15);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::never())
            ->method('persist');
        $event = new PrePersistEventArgs($organization, $entityManager);

        $this->listener->prePersist($organization, $event);
    }

    public function testPostFlush(): void
    {
        $organization = new Organization();
        ReflectionUtil::setPropertyValue($organization, 'id', 12);

        $customerGroup = new CustomerGroup();
        $customerGroup->setOrganization($organization);
        ReflectionUtil::setPropertyValue($customerGroup, 'id', 11);

        ReflectionUtil::setPropertyValue($this->listener, 'organizationCustomerGroups', [$customerGroup]);
        $this->configManager->expects(self::once())
            ->method('set')
            ->with(Configuration::getConfigKeyByName(Configuration::ANONYMOUS_CUSTOMER_GROUP), 11, 12);
        $this->configManager->expects(self::once())
            ->method('flush');

        $this->listener->postFlush();
    }
}
