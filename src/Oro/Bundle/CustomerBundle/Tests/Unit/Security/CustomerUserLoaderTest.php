<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Security;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerUserRepository;
use Oro\Bundle\CustomerBundle\Security\CustomerUserLoader;

class CustomerUserLoaderTest extends \PHPUnit\Framework\TestCase
{
    /** @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject */
    private $doctrine;

    /** @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject */
    private $configManager;

    /** @var CustomerUserLoader */
    private $userLoader;

    protected function setUp()
    {
        $this->doctrine = $this->createMock(ManagerRegistry::class);
        $this->configManager = $this->createMock(ConfigManager::class);

        $this->userLoader = new CustomerUserLoader($this->doctrine, $this->configManager);
    }

    /**
     * @return CustomerUserRepository|\PHPUnit\Framework\MockObject\MockObject
     */
    private function expectGetRepository()
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $this->doctrine->expects(self::atLeastOnce())
            ->method('getManagerForClass')
            ->with(CustomerUser::class)
            ->willReturn($em);

        $repository = $this->createMock(CustomerUserRepository::class);
        $em->expects(self::atLeastOnce())
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
        self::assertEquals(CustomerUser::class, $this->userLoader->getUserClass());
    }

    /**
     * @dataProvider findUserDataProvider
     */
    public function testLoadUserByUsername($user)
    {
        $username = 'test';
        $caseInsensitiveEmailAddressesEnabled = true;

        $this->configManager->expects(self::once())
            ->method('get')
            ->with('oro_customer.case_insensitive_email_addresses_enabled')
            ->willReturn($caseInsensitiveEmailAddressesEnabled);

        $repository = $this->expectGetRepository();
        $repository->expects(self::once())
            ->method('findUserByEmail')
            ->with($username, $caseInsensitiveEmailAddressesEnabled)
            ->willReturn($user);

        self::assertSame($user, $this->userLoader->loadUserByUsername($username));
    }

    /**
     * @dataProvider findUserDataProvider
     */
    public function testLoadUserByEmail($user)
    {
        $email = 'test@example.com';
        $caseInsensitiveEmailAddressesEnabled = true;

        $this->configManager->expects(self::once())
            ->method('get')
            ->with('oro_customer.case_insensitive_email_addresses_enabled')
            ->willReturn($caseInsensitiveEmailAddressesEnabled);

        $repository = $this->expectGetRepository();
        $repository->expects(self::once())
            ->method('findUserByEmail')
            ->with($email, $caseInsensitiveEmailAddressesEnabled)
            ->willReturn($user);

        self::assertSame($user, $this->userLoader->loadUserByEmail($email));
    }

    /**
     * @dataProvider findUserDataProvider
     */
    public function testLoadUser($user)
    {
        $login = 'test@example.com';
        $caseInsensitiveEmailAddressesEnabled = false;

        $this->configManager->expects(self::once())
            ->method('get')
            ->with('oro_customer.case_insensitive_email_addresses_enabled')
            ->willReturn($caseInsensitiveEmailAddressesEnabled);

        $repository = $this->expectGetRepository();
        $repository->expects(self::once())
            ->method('findUserByEmail')
            ->with($login, $caseInsensitiveEmailAddressesEnabled)
            ->willReturn($user);

        self::assertSame($user, $this->userLoader->loadUser($login));
    }
}
