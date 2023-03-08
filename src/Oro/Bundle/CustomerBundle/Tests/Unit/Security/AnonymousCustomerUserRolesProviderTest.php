<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Security;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Security\AnonymousCustomerUserRolesProvider;
use Oro\Bundle\CustomerBundle\Tests\Unit\Entity\Stub\WebsiteStub;
use Oro\Bundle\CustomerBundle\Tests\Unit\Stub\CustomerUserRoleProxyStub;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Component\Testing\Unit\ORM\OrmTestCase;

class AnonymousCustomerUserRolesProviderTest extends OrmTestCase
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var WebsiteManager|\PHPUnit\Framework\MockObject\MockObject */
    private $websiteManager;

    /** @var AnonymousCustomerUserRolesProvider */
    private $rolesProvider;

    protected function setUp(): void
    {
        $this->em = $this->getTestEntityManager();
        $this->em->getConfiguration()->setMetadataDriverImpl(new AnnotationDriver(new AnnotationReader()));

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects(self::any())
            ->method('getManagerForClass')
            ->with(CustomerUserRole::class)
            ->willReturn($this->em);

        $this->websiteManager = $this->createMock(WebsiteManager::class);

        $this->rolesProvider = new AnonymousCustomerUserRolesProvider($this->websiteManager, $doctrine);
    }

    public function testGetRolesWhenNoCurrentWebsite(): void
    {
        $this->websiteManager->expects(self::once())
            ->method('getCurrentWebsite')
            ->willReturn(null);

        self::assertSame([], $this->rolesProvider->getRoles());
    }

    public function testGetRolesWhenNoGuestRole(): void
    {
        $website = $this->createMock(WebsiteStub::class);

        $this->websiteManager->expects(self::once())
            ->method('getCurrentWebsite')
            ->willReturn($website);

        $website->expects(self::once())
            ->method('getGuestRole')
            ->willReturn(null);

        self::assertSame([], $this->rolesProvider->getRoles());
    }

    public function testGetRolesWhenGuestRoleHydratedButRoleNameIsEmpty(): void
    {
        $website = $this->createMock(WebsiteStub::class);
        $guestRole = $this->createMock(CustomerUserRole::class);

        $this->websiteManager->expects(self::once())
            ->method('getCurrentWebsite')
            ->willReturn($website);

        $website->expects(self::once())
            ->method('getGuestRole')
            ->willReturn($guestRole);

        $guestRole->expects(self::once())
            ->method('getRole')
            ->willReturn('');

        self::assertSame([], $this->rolesProvider->getRoles());
    }

    public function testGetRolesWhenGuestRoleHydrated(): void
    {
        $website = $this->createMock(WebsiteStub::class);
        $guestRoleName = 'TEST_ROLE';
        $guestRole = $this->createMock(CustomerUserRole::class);

        $this->websiteManager->expects(self::once())
            ->method('getCurrentWebsite')
            ->willReturn($website);

        $website->expects(self::once())
            ->method('getGuestRole')
            ->willReturn($guestRole);

        $guestRole->expects(self::once())
            ->method('getRole')
            ->willReturn($guestRoleName);

        self::assertSame([$guestRoleName], $this->rolesProvider->getRoles());
    }

    public function testGetRolesWhenGuestRoleIsProxyAndItIsHydrated(): void
    {
        $website = $this->createMock(WebsiteStub::class);
        $guestRoleName = 'TEST_ROLE';
        $guestRole = $this->createMock(CustomerUserRoleProxyStub::class);

        $this->websiteManager->expects(self::once())
            ->method('getCurrentWebsite')
            ->willReturn($website);

        $website->expects(self::once())
            ->method('getGuestRole')
            ->willReturn($guestRole);

        $guestRole->expects(self::once())
            ->method('__isInitialized')
            ->willReturn(true);
        $guestRole->expects(self::once())
            ->method('getRole')
            ->willReturn($guestRoleName);

        self::assertSame([$guestRoleName], $this->rolesProvider->getRoles());
    }

    public function testGetRolesWhenGuestRoleNotHydrated(): void
    {
        $website = $this->createMock(WebsiteStub::class);
        $guestRoleId = 123;
        $guestRoleName = 'TEST_ROLE';
        $guestRole = $this->createMock(CustomerUserRoleProxyStub::class);

        $this->websiteManager->expects(self::once())
            ->method('getCurrentWebsite')
            ->willReturn($website);

        $website->expects(self::once())
            ->method('getGuestRole')
            ->willReturn($guestRole);

        $guestRole->expects(self::once())
            ->method('__isInitialized')
            ->willReturn(false);
        $guestRole->expects(self::once())
            ->method('getId')
            ->willReturn($guestRoleId);

        $this->setQueryExpectation(
            $this->getDriverConnectionMock($this->em),
            'SELECT o0_.role AS role_0 FROM oro_customer_user_role o0_ WHERE o0_.id = ?',
            [
                ['role_0' => $guestRoleName]
            ],
            [1 => $guestRoleId],
            [1 => \PDO::PARAM_INT]
        );

        self::assertSame([$guestRoleName], $this->rolesProvider->getRoles());
    }

    public function testGetRolesWhenGuestRoleNotHydratedAndRoleNotFoundInDatabase(): void
    {
        $website = $this->createMock(WebsiteStub::class);
        $guestRoleId = 123;
        $guestRole = $this->createMock(CustomerUserRoleProxyStub::class);

        $this->websiteManager->expects(self::once())
            ->method('getCurrentWebsite')
            ->willReturn($website);

        $website->expects(self::once())
            ->method('getGuestRole')
            ->willReturn($guestRole);

        $guestRole->expects(self::once())
            ->method('__isInitialized')
            ->willReturn(false);
        $guestRole->expects(self::once())
            ->method('getId')
            ->willReturn($guestRoleId);

        $this->setQueryExpectation(
            $this->getDriverConnectionMock($this->em),
            'SELECT o0_.role AS role_0 FROM oro_customer_user_role o0_ WHERE o0_.id = ?',
            [],
            [1 => $guestRoleId],
            [1 => \PDO::PARAM_INT]
        );

        self::assertSame([], $this->rolesProvider->getRoles());
    }
}
