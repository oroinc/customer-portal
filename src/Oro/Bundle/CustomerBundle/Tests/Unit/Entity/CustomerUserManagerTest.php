<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Entity;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserSettings;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerUserRepository;
use Oro\Bundle\CustomerBundle\Mailer\Processor;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\LocaleBundle\Helper\LocalizationHelper;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\UserBundle\Entity\Repository\AbstractUserRepository;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Component\Testing\Unit\EntityTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class CustomerUserManagerTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    const USER_CLASS = 'Oro\Bundle\CustomerBundle\Entity\CustomerUser';

    const DEFAULT_LOCALIZATION = 'en';

    const CURRENT_LOCALIZATION = 'fr_FR';

    /**
     * @var CustomerUserManager
     */
    protected $userManager;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $om;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $registry;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $ef;

    /**
     * @var FrontendHelper|\PHPUnit\Framework\MockObject\MockObject
     */
    private $frontendHelper;

    /**
     * @var LocalizationHelper|\PHPUnit\Framework\MockObject\MockObject
     */
    private $localizationHelper;

    /**
     * @var WebsiteManager|\PHPUnit\Framework\MockObject\MockObject
     */
    private $websiteManager;

    /**
     * @var TokenAccessorInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $tokenAccessor;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $configManager;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $emailProcessor;

    protected function setUp()
    {
        $this->ef = $this->createMock(EncoderFactoryInterface::class);
        $this->om = $this->createMock(ObjectManager::class);
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->configManager = $this->createMock(ConfigManager::class);
        $this->emailProcessor = $this->createMock(Processor::class);
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->any())
            ->method('get')
            ->will(
                $this->returnValueMap(
                    [
                        [
                            'oro_config.manager',
                            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
                            $this->configManager
                        ],
                        [
                            'oro_customer.mailer.processor',
                            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
                            $this->emailProcessor
                        ],
                        [
                            'oro_security.token_accessor',
                            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
                            $this->tokenAccessor
                        ]
                    ]
                )
            );

        $this->frontendHelper = $this->createMock(FrontendHelper::class);
        $this->localizationHelper = $this->createMock(LocalizationHelper::class);
        $this->websiteManager = $this->createMock(WebsiteManager::class);

        $this->userManager = new CustomerUserManager(
            static::USER_CLASS,
            $this->registry,
            $this->ef,
            $this->frontendHelper,
            $this->localizationHelper,
            $this->websiteManager
        );
        $this->userManager->setContainer($container);
    }

    public function testConfirmRegistration()
    {
        $password = 'test';

        $user = new CustomerUser();
        $user->setConfirmed(false);
        $user->setPlainPassword($password);

        $this->emailProcessor->expects($this->once())
            ->method('sendWelcomeNotification')
            ->with($user);

        $this->userManager->confirmRegistration($user);

        $this->assertTrue($user->isConfirmed());
    }

    public function testConfirmRegistrationByAdmin()
    {
        $user = new CustomerUser();
        $user->setConfirmed(false);

        $this->emailProcessor->expects($this->once())
            ->method('sendWelcomeForRegisteredByAdminNotification')
            ->with($user);

        $this->userManager->confirmRegistrationByAdmin($user);

        $this->assertTrue($user->isConfirmed());
    }

    public function testSendWelcomeEmail()
    {
        $password = 'test';

        $user = new CustomerUser();
        $user->setPlainPassword($password);

        $this->emailProcessor->expects($this->once())
            ->method('sendWelcomeNotification')
            ->with($user);

        $this->userManager->sendWelcomeEmail($user);
    }

    public function testSendWelcomeRegisteredByAdminEmail()
    {
        $user = new CustomerUser();

        $this->emailProcessor->expects($this->once())
            ->method('sendWelcomeForRegisteredByAdminNotification')
            ->with($user);

        $this->userManager->sendWelcomeRegisteredByAdminEmail($user);
    }

    public function testGeneratePassword()
    {
        $password = $this->userManager->generatePassword(10);
        $this->assertNotEmpty($password);
        $this->assertRegExp('/\w+/', $password);
        $this->assertLessThanOrEqual(10, strlen($password));
    }

    public function testRegisterConfirmationRequiredNotFrontendRequest()
    {
        $password = 'test1Q';

        $user = new CustomerUser();
        $user->setEnabled(false);
        $user->setPlainPassword($password);

        $this->frontendHelper->expects($this->once())
            ->method('isFrontendRequest')
            ->willReturn(false);

        $this->websiteManager->expects($this->never())
            ->method($this->anything());

        $this->configManager->expects($this->once())
            ->method('get')
            ->with('oro_customer.confirmation_required')
            ->will($this->returnValue(true));

        $encoder = $this->createMock(PasswordEncoderInterface::class);
        $encoder->expects($this->once())
            ->method('encodePassword')
            ->with($user->getPlainPassword(), $user->getSalt());

        $this->ef->expects($this->once())
            ->method('getEncoder')
            ->with($user)
            ->will($this->returnValue($encoder));

        $this->registry->expects($this->any())
            ->method('getManagerForClass')
            ->will($this->returnValue($this->om));

        $this->om->expects($this->once())->method('persist')->with($this->equalTo($user));
        $this->om->expects($this->once())->method('flush');

        $this->emailProcessor->expects($this->once())
            ->method('sendConfirmationEmail')
            ->with($user);

        $this->userManager->register($user);

        $this->assertFalse($user->isEnabled());
        $this->assertNotEmpty($user->getConfirmationToken());
    }

    public function testRegisterConfirmationNotRequiredWhenWebsiteSettingsExist()
    {
        $this->frontendHelper->expects($this->once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        /** @var Website $website */
        $website = $this->getEntity(Website::class);
        $defaultLocalization = $this->getEntity(
            Localization::class,
            ['id' => 2, 'formattingCode' => self::DEFAULT_LOCALIZATION]
        );

        /** @var CustomerUser $user */
        $user = $this->getEntity(
            CustomerUser::class,
            [
                'websiteSettings' => (new CustomerUserSettings($website))->setLocalization($defaultLocalization),
                'confirmed' => false,
                'plainPassword' => 'test1Q'
            ]
        );

        $this->websiteManager
            ->expects($this->once())
            ->method('getCurrentWebsite')
            ->willReturn($website);

        $currentLocalization = $this->getEntity(
            Localization::class,
            ['id' => 1, 'formattingCode' => self::CURRENT_LOCALIZATION]
        );

        $this->localizationHelper
            ->expects($this->once())
            ->method('getCurrentLocalization')
            ->willReturn($currentLocalization);

        $this->configManager->expects($this->exactly(1))
            ->method('get')
            ->willReturnMap(
                [
                    ['oro_customer.confirmation_required', false, false, null, false],
                ]
            );

        $this->emailProcessor->expects($this->once())
            ->method('sendWelcomeNotification')
            ->with($user);

        $expectedWebsiteSettings = new CustomerUserSettings($website);
        $expectedWebsiteSettings->setLocalization($currentLocalization);
        $expectedWebsiteSettings->setCustomerUser($user);

        $this->userManager->register($user);

        $this->assertTrue($user->isConfirmed());

        self::assertEquals($expectedWebsiteSettings, $user->getWebsiteSettings($website));
    }

    public function testRegisterConfirmationNotRequiredWhenWebsiteSettingsNotExist()
    {
        $this->frontendHelper->expects($this->once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $website = new Website();

        /** @var CustomerUser $user */
        $user = $this->getEntity(
            CustomerUser::class,
            [
                'confirmed' => false,
                'plainPassword' => 'test1Q'
            ]
        );

        $this->websiteManager
            ->expects($this->any())
            ->method('getCurrentWebsite')
            ->willReturn($website);

        $currentLocalization = $this->getEntity(
            Localization::class,
            ['id' => 1, 'formattingCode' => self::CURRENT_LOCALIZATION]
        );

        $this->localizationHelper
            ->expects($this->once())
            ->method('getCurrentLocalization')
            ->willReturn($currentLocalization);

        $this->configManager->expects($this->exactly(1))
            ->method('get')
            ->willReturnMap(
                [
                    ['oro_customer.confirmation_required', false, false, null, false],
                ]
            );

        $this->emailProcessor->expects($this->once())
            ->method('sendWelcomeNotification')
            ->with($user);

        $expectedWebsiteSettings = new CustomerUserSettings($website);
        $expectedWebsiteSettings->setLocalization($currentLocalization);
        $expectedWebsiteSettings->setCustomerUser($user);

        $this->userManager->register($user);

        $this->assertTrue($user->isConfirmed());
        $this->assertEquals($expectedWebsiteSettings, $user->getWebsiteSettings($website));
    }

    public function testRegisterConfirmationNotRequired()
    {
        $password = 'test1Q';

        $user = new CustomerUser();
        $user->setConfirmed(false);
        $user->setPlainPassword($password);

        $this->configManager->expects($this->exactly(1))
            ->method('get')
            ->willReturnMap(
                [
                    ['oro_customer.confirmation_required', false, false, null, false],
                ]
            );

        $this->emailProcessor->expects($this->once())
            ->method('sendWelcomeNotification')
            ->with($user);

        $this->userManager->register($user);

        $this->assertTrue($user->isConfirmed());
    }

    public function testSendResetPasswordEmail()
    {
        $user = new CustomerUser();
        $this->emailProcessor->expects($this->once())
            ->method('sendResetPasswordEmail')
            ->with($user);
        $this->userManager->sendResetPasswordEmail($user);
    }

    /**
     * @dataProvider requiredDataProvider
     * @param bool $required
     */
    public function testIsConfirmationRequired($required)
    {
        $this->configManager->expects($this->once())
            ->method('get')
            ->with('oro_customer.confirmation_required')
            ->will($this->returnValue($required));

        $this->assertEquals($required, $this->userManager->isConfirmationRequired());
    }

    public function testSaveDisabledCustomerWithoutRole()
    {
        $password = 'password';
        $encodedPassword = 'encodedPassword';
        $email = 'test@test.com';

        $customerUser = new CustomerUser();
        $customerUser
            ->setUsername($email)
            ->setEmail($email)
            ->setPlainPassword($password);

        $customerUser->setEnabled(false);

        $encoder = $this->createMock('Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface');
        $encoder->expects($this->once())
            ->method('encodePassword')
            ->with($customerUser->getPlainPassword(), $customerUser->getSalt())
            ->will($this->returnValue($encodedPassword));

        $this->ef->expects($this->once())
            ->method('getEncoder')
            ->with($customerUser)
            ->will($this->returnValue($encoder));

        $this->registry->expects($this->any())
            ->method('getManagerForClass')
            ->will($this->returnValue($this->om));

        $this->om->expects($this->once())->method('persist')->with($this->equalTo($customerUser));
        $this->om->expects($this->once())->method('flush');

        $this->userManager->updateUser($customerUser);

        $this->assertEquals($email, $customerUser->getEmail());
        $this->assertEquals($encodedPassword, $customerUser->getPassword());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Enabled customer has not default role
     */
    public function testUpdateUserWithException()
    {
        $password = 'password';
        $email = 'test@test.com';

        $customerUser = new CustomerUser();
        $customerUser
            ->setUsername($email)
            ->setEmail($email)
            ->setPlainPassword($password);

        $customerUser->setEnabled(true);

        $this->userManager->updateUser($customerUser);
    }

    /**
     * @return array
     */
    public function requiredDataProvider()
    {
        return [
            [true],
            [false]
        ];
    }

    public function testFindUserBy()
    {
        $repository = $this->createMock('Doctrine\Common\Persistence\ObjectRepository');
        $this->om
            ->expects($this->any())
            ->method('getRepository')
            ->withAnyParameters()
            ->will($this->returnValue($repository));

        $class = $this->createMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $this->om
            ->expects($this->any())
            ->method('getClassMetadata')
            ->with($this->equalTo(static::USER_CLASS))
            ->will($this->returnValue($class));

        $this->registry->expects($this->any())
            ->method('getManagerForClass')
            ->will($this->returnValue($this->om));

        $criteria = ['id' => 0];

        $repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo(array_merge($criteria, ['isGuest' => false])));

        $this->userManager->findUserBy($criteria);
    }

    public function testFindUserByEmail()
    {
        $email = 'Test@test.com';

        $user = new CustomerUser();
        $user->setEmail($email);

        $this->assertRepositoryCalled($user);
        $this->assertConfigManagerCalled();

        self::assertSame($user, $this->userManager->findUserByEmail($email));
    }

    public function testFindUserByUsername()
    {
        $email = 'Test@test.com';

        $user = new CustomerUser();
        $user->setEmail($email);

        $this->assertRepositoryCalled($user);
        $this->assertConfigManagerCalled();

        self::assertSame($user, $this->userManager->findUserByUsername($email));
    }

    public function testFindUserByEmailWithWebsiteOrganization()
    {
        $email = 'Test@test.com';

        $user = new CustomerUser();
        $user->setEmail($email);

        $website = new Website();
        $org = new Organization();
        $website->setOrganization($org);

        $this->assertConfigManagerCalled();

        $this->registry->expects($this->any())
            ->method('getManagerForClass')
            ->willReturn($this->om);

        $repository = $this->createMock(CustomerUserRepository::class);
        $repository->expects($this->once())
            ->method('findUserByEmailAndOrganization')
            ->with($user->getEmail(), $org, true)
            ->willReturn($user);

        $this->om->expects($this->any())
            ->method('getRepository')
            ->with($this->userManager->getClass())
            ->willReturn($repository);

        $this->websiteManager->expects($this->once())
            ->method('getCurrentWebsite')
            ->willReturn($website);

        $this->tokenAccessor->expects($this->never())
            ->method('getOrganization');

        $this->assertSame($user, $this->userManager->findUserByUsername($email));
    }

    public function testFindUserByEmailWithTokenOrganization()
    {
        $email = 'Test@test.com';

        $user = new CustomerUser();
        $user->setEmail($email);

        $org = new Organization();

        $this->assertConfigManagerCalled();

        $this->registry->expects($this->any())
            ->method('getManagerForClass')
            ->willReturn($this->om);

        $repository = $this->createMock(CustomerUserRepository::class);
        $repository->expects($this->once())
            ->method('findUserByEmailAndOrganization')
            ->with($user->getEmail(), $org, true)
            ->willReturn($user);

        $this->om->expects($this->any())
            ->method('getRepository')
            ->with($this->userManager->getClass())
            ->willReturn($repository);

        $this->websiteManager->expects($this->once())
            ->method('getCurrentWebsite')
            ->willReturn(null);

        $this->tokenAccessor->expects($this->once())
            ->method('getOrganization')
            ->willReturn($org);

        $this->assertSame($user, $this->userManager->findUserByUsername($email));
    }

    /**
     * @param CustomerUser $user
     */
    private function assertRepositoryCalled(CustomerUser $user)
    {
        $this->registry
            ->expects(self::any())
            ->method('getManagerForClass')
            ->willReturn($this->om);

        $this->om
            ->expects(self::any())
            ->method('getRepository')
            ->with($this->userManager->getClass())
            ->willReturn($repository = $this->createMock(AbstractUserRepository::class));

        $repository
            ->expects(self::once())
            ->method('findUserByEmail')
            ->with($user->getEmail(), true)
            ->willReturn($user);
    }

    /**
     * @param bool $result
     */
    private function assertConfigManagerCalled(bool $result = true)
    {
        $this->configManager
            ->expects(self::once())
            ->method('get')
            ->with('oro_customer.case_insensitive_email_addresses_enabled')
            ->willReturn($result);
    }
}
