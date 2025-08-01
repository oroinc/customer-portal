<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Security;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerUserRepository;
use Oro\Bundle\CustomerBundle\Security\CustomerUserLoader;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessor;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class CustomerUserLoaderTest extends TestCase
{
    private ManagerRegistry&MockObject $doctrine;
    private ConfigManager&MockObject $configManager;
    private TokenAccessor&MockObject $tokenAccessor;
    private WebsiteManager&MockObject $websiteManager;
    private CustomerUserLoader $userLoader;

    #[\Override]
    protected function setUp(): void
    {
        $this->doctrine = $this->createMock(ManagerRegistry::class);
        $this->configManager = $this->createMock(ConfigManager::class);
        $this->tokenAccessor = $this->createMock(TokenAccessor::class);
        $this->websiteManager = $this->createMock(WebsiteManager::class);

        $this->userLoader = new CustomerUserLoader(
            $this->doctrine,
            $this->configManager,
            $this->tokenAccessor,
            $this->websiteManager
        );
    }

    private function expectGetRepository(): CustomerUserRepository|MockObject
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $this->doctrine->expects($this->atLeastOnce())
            ->method('getManagerForClass')
            ->with(CustomerUser::class)
            ->willReturn($em);

        $repository = $this->createMock(CustomerUserRepository::class);
        $em->expects($this->atLeastOnce())
            ->method('getRepository')
            ->with(CustomerUser::class)
            ->willReturn($repository);

        return $repository;
    }

    public function findUserDataProvider(): array
    {
        return [
            [$this->createMock(CustomerUser::class)],
            [null]
        ];
    }

    public function testGetUserClass(): void
    {
        $this->assertEquals(CustomerUser::class, $this->userLoader->getUserClass());
    }

    /**
     * @dataProvider findUserDataProvider
     */
    public function testLoadUserByUsername(?CustomerUser $user): void
    {
        $username = 'test';
        $caseInsensitiveEmailAddressesEnabled = true;

        $organization = new Organization();
        $this->tokenAccessor->expects($this->once())
            ->method('getOrganization')
            ->willReturn($organization);

        $this->configManager->expects($this->once())
            ->method('get')
            ->with('oro_customer.case_insensitive_email_addresses_enabled')
            ->willReturn($caseInsensitiveEmailAddressesEnabled);

        $repository = $this->expectGetRepository();
        $repository->expects($this->once())
            ->method('findUserByEmailAndOrganization')
            ->with($username, $organization, $caseInsensitiveEmailAddressesEnabled)
            ->willReturn($user);
        $repository->expects($this->never())
            ->method('findUserByEmail');

        $this->assertSame($user, $this->userLoader->loadUserByIdentifier($username));
    }

    /**
     * @dataProvider findUserDataProvider
     */
    public function testLoadUserByUsernameWithoutOrganization(?CustomerUser $user): void
    {
        $username = 'test';
        $caseInsensitiveEmailAddressesEnabled = true;

        $this->tokenAccessor->expects($this->once())
            ->method('getOrganization')
            ->willReturn(null);

        $this->configManager->expects($this->once())
            ->method('get')
            ->with('oro_customer.case_insensitive_email_addresses_enabled')
            ->willReturn($caseInsensitiveEmailAddressesEnabled);

        $repository = $this->expectGetRepository();
        $repository->expects($this->never())
            ->method('findUserByEmailAndOrganization');
        $repository->expects($this->once())
            ->method('findUserByEmail')
            ->with($username, $caseInsensitiveEmailAddressesEnabled)
            ->willReturn($user);

        $this->assertSame($user, $this->userLoader->loadUserByIdentifier($username));
    }

    /**
     * @dataProvider findUserDataProvider
     */
    public function testLoadUserByEmail(?CustomerUser $user): void
    {
        $email = 'test@example.com';
        $caseInsensitiveEmailAddressesEnabled = true;

        $organization = new Organization();
        $this->tokenAccessor->expects($this->once())
            ->method('getOrganization')
            ->willReturn($organization);
        $this->websiteManager->expects($this->never())
            ->method('getCurrentWebsite');

        $this->configManager->expects($this->once())
            ->method('get')
            ->with('oro_customer.case_insensitive_email_addresses_enabled')
            ->willReturn($caseInsensitiveEmailAddressesEnabled);

        $repository = $this->expectGetRepository();
        $repository->expects($this->once())
            ->method('findUserByEmailAndOrganization')
            ->with($email, $organization, $caseInsensitiveEmailAddressesEnabled)
            ->willReturn($user);
        $repository->expects($this->never())
            ->method('findUserByEmail');

        $this->assertSame($user, $this->userLoader->loadUserByEmail($email));
    }

    public function testLoadUserByEmailWithOrganizationFromWebsite(): void
    {
        $email = 'test@example.com';
        $caseInsensitiveEmailAddressesEnabled = true;

        $organization = new Organization();
        $this->tokenAccessor->expects($this->once())
            ->method('getOrganization')
            ->willReturn(null);
        $website = new Website();
        $website->setOrganization($organization);
        $this->websiteManager->expects($this->once())
            ->method('getCurrentWebsite')
            ->willReturn($website);

        $this->configManager->expects($this->once())
            ->method('get')
            ->with('oro_customer.case_insensitive_email_addresses_enabled')
            ->willReturn($caseInsensitiveEmailAddressesEnabled);

        $repository = $this->expectGetRepository();
        $user = new CustomerUser();
        $repository->expects($this->once())
            ->method('findUserByEmailAndOrganization')
            ->with($email, $organization, $caseInsensitiveEmailAddressesEnabled)
            ->willReturn($user);
        $repository->expects($this->never())
            ->method('findUserByEmail');

        $this->assertSame($user, $this->userLoader->loadUserByEmail($email));
    }

    /**
     * @dataProvider findUserDataProvider
     */
    public function testLoadUserByEmailWithoutOrganization(?CustomerUser $user): void
    {
        $email = 'test@example.com';
        $caseInsensitiveEmailAddressesEnabled = true;

        $this->tokenAccessor->expects($this->once())
            ->method('getOrganization')
            ->willReturn(null);
        $this->websiteManager->expects($this->once())
            ->method('getCurrentWebsite')
            ->willReturn(null);

        $this->configManager->expects($this->once())
            ->method('get')
            ->with('oro_customer.case_insensitive_email_addresses_enabled')
            ->willReturn($caseInsensitiveEmailAddressesEnabled);

        $repository = $this->expectGetRepository();
        $repository->expects($this->never())
            ->method('findUserByEmailAndOrganization');
        $repository->expects($this->once())
            ->method('findUserByEmail')
            ->with($email, $caseInsensitiveEmailAddressesEnabled)
            ->willReturn($user);

        $this->assertSame($user, $this->userLoader->loadUserByEmail($email));
    }

    /**
     * @dataProvider findUserDataProvider
     */
    public function testLoadUser(?CustomerUser $user): void
    {
        $login = 'test@example.com';
        $caseInsensitiveEmailAddressesEnabled = false;

        $organization = new Organization();
        $this->tokenAccessor->expects($this->once())
            ->method('getOrganization')
            ->willReturn($organization);

        $this->configManager->expects($this->once())
            ->method('get')
            ->with('oro_customer.case_insensitive_email_addresses_enabled')
            ->willReturn($caseInsensitiveEmailAddressesEnabled);

        $repository = $this->expectGetRepository();
        $repository->expects($this->once())
            ->method('findUserByEmailAndOrganization')
            ->with($login, $organization, $caseInsensitiveEmailAddressesEnabled)
            ->willReturn($user);
        $repository->expects($this->never())
            ->method('findUserByEmail');

        $this->assertSame($user, $this->userLoader->loadUser($login));
    }

    /**
     * @dataProvider findUserDataProvider
     */
    public function testLoadUserWithoutOrganization(?CustomerUser $user): void
    {
        $login = 'test@example.com';
        $caseInsensitiveEmailAddressesEnabled = false;

        $this->tokenAccessor->expects($this->once())
            ->method('getOrganization')
            ->willReturn(null);

        $this->configManager->expects($this->once())
            ->method('get')
            ->with('oro_customer.case_insensitive_email_addresses_enabled')
            ->willReturn($caseInsensitiveEmailAddressesEnabled);

        $repository = $this->expectGetRepository();
        $repository->expects($this->never())
            ->method('findUserByEmailAndOrganization');
        $repository->expects($this->once())
            ->method('findUserByEmail')
            ->with($login, $caseInsensitiveEmailAddressesEnabled)
            ->willReturn($user);

        $this->assertSame($user, $this->userLoader->loadUser($login));
    }
}
