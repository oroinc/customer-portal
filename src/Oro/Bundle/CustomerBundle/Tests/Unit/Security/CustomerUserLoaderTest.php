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

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class CustomerUserLoaderTest extends \PHPUnit\Framework\TestCase
{
    /** @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject */
    private $doctrine;

    /** @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject */
    private $configManager;

    /** @var TokenAccessor|\PHPUnit\Framework\MockObject\MockObject */
    private $tokenAccessor;

    /** @var WebsiteManager|\PHPUnit\Framework\MockObject\MockObject */
    private $websiteManager;

    /** @var CustomerUserLoader */
    private $userLoader;

    /**
     * {@inheritdoc}
     */
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

    /**
     * @return CustomerUserRepository|\PHPUnit\Framework\MockObject\MockObject
     */
    private function expectGetRepository()
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

    /**
     * @return array
     */
    public function findUserDataProvider()
    {
        return [
            [$this->createMock(CustomerUser::class)],
            [null]
        ];
    }

    public function testGetUserClass()
    {
        $this->assertEquals(CustomerUser::class, $this->userLoader->getUserClass());
    }

    /**
     * @dataProvider findUserDataProvider
     * @param CustomerUser|\PHPUnit\Framework\MockObject\MockObject $user
     */
    public function testLoadUserByUsername($user)
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

        $this->assertSame($user, $this->userLoader->loadUserByUsername($username));
    }

    /**
     * @dataProvider findUserDataProvider
     * @param CustomerUser|\PHPUnit\Framework\MockObject\MockObject $user
     */
    public function testLoadUserByUsernameWithoutOrganization($user)
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

        $this->assertSame($user, $this->userLoader->loadUserByUsername($username));
    }

    /**
     * @dataProvider findUserDataProvider
     * @param CustomerUser|\PHPUnit\Framework\MockObject\MockObject $user
     */
    public function testLoadUserByEmail($user)
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

    public function testLoadUserByEmailWithOrganizationFromWebsite()
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
     * @param CustomerUser|\PHPUnit\Framework\MockObject\MockObject $user
     */
    public function testLoadUserByEmailWithoutOrganization($user)
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
     * @param CustomerUser|\PHPUnit\Framework\MockObject\MockObject $user
     */
    public function testLoadUser($user)
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
     * @param CustomerUser|\PHPUnit\Framework\MockObject\MockObject $user
     */
    public function testLoadUserWithoutOrganization($user)
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
