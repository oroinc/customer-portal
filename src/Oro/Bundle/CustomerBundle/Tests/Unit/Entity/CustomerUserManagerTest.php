<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Entity;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserSettings;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerUserRepository;
use Oro\Bundle\CustomerBundle\Mailer\Processor;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\LocaleBundle\Helper\LocalizationHelper;
use Oro\Bundle\UserBundle\Security\UserLoaderInterface;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Component\DependencyInjection\ServiceLink;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class CustomerUserManagerTest extends \PHPUnit\Framework\TestCase
{
    /** @var UserLoaderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $userLoader;

    /** @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject */
    private $doctrine;

    /** @var EncoderFactoryInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $encoderFactory;

    /** @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject */
    private $configManager;

    /** var Processor|\PHPUnit\Framework\MockObject\MockObject */
    private $emailProcessor;

    /** @var FrontendHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $frontendHelper;

    /** @var LocalizationHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $localizationHelper;

    /** @var WebsiteManager|\PHPUnit\Framework\MockObject\MockObject */
    private $websiteManager;

    /** @var LoggerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $logger;

    /** @var CustomerUserManager */
    private $userManager;

    protected function setUp(): void
    {
        $this->userLoader = $this->createMock(UserLoaderInterface::class);
        $this->doctrine = $this->createMock(ManagerRegistry::class);
        $this->encoderFactory = $this->createMock(EncoderFactoryInterface::class);
        $this->configManager = $this->createMock(ConfigManager::class);
        $this->emailProcessor = $this->createMock(Processor::class);
        $this->frontendHelper = $this->createMock(FrontendHelper::class);
        $this->localizationHelper = $this->createMock(LocalizationHelper::class);
        $this->websiteManager = $this->createMock(WebsiteManager::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->userLoader->expects(self::any())
            ->method('getUserClass')
            ->willReturn(CustomerUser::class);

        $emailProcessorLink = $this->createMock(ServiceLink::class);
        $emailProcessorLink->expects(self::any())
            ->method('getService')
            ->willReturn($this->emailProcessor);

        $this->userManager = new CustomerUserManager(
            $this->userLoader,
            $this->doctrine,
            $this->encoderFactory,
            $this->configManager,
            $emailProcessorLink,
            $this->frontendHelper,
            $this->localizationHelper,
            $this->websiteManager,
            $this->logger
        );
    }

    /**
     * @return EntityManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private function expectGetEntityManager()
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $this->doctrine->expects(self::atLeastOnce())
            ->method('getManagerForClass')
            ->with(CustomerUser::class)
            ->willReturn($em);

        return $em;
    }

    /**
     * @param EntityManagerInterface|\PHPUnit\Framework\MockObject\MockObject $em
     *
     * @return CustomerUserRepository|\PHPUnit\Framework\MockObject\MockObject
     */
    private function expectGetRepository($em)
    {
        $repository = $this->createMock(CustomerUserRepository::class);
        $em->expects(self::atLeastOnce())
            ->method('getRepository')
            ->with(CustomerUser::class)
            ->willReturn($repository);

        return $repository;
    }

    /**
     * @param CustomerUser $user
     *
     * @return PasswordEncoderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private function expectGetPasswordEncoder(CustomerUser $user)
    {
        $encoder = $this->createMock(PasswordEncoderInterface::class);
        $this->encoderFactory->expects(self::once())
            ->method('getEncoder')
            ->with($user)
            ->willReturn($encoder);

        return $encoder;
    }

    public function testConfirmRegistration()
    {
        $user = new CustomerUser();
        $user->setConfirmed(false);

        $this->emailProcessor->expects(self::once())
            ->method('sendWelcomeNotification')
            ->with(self::identicalTo($user));

        $this->userManager->confirmRegistration($user);

        self::assertTrue($user->isConfirmed());
        self::assertNotEmpty($user->getConfirmationToken());
    }

    public function testConfirmRegistrationByAdmin()
    {
        $user = new CustomerUser();
        $user->setConfirmed(false);

        $this->emailProcessor->expects(self::once())
            ->method('sendWelcomeForRegisteredByAdminNotification')
            ->with(self::identicalTo($user));

        $this->userManager->confirmRegistrationByAdmin($user);

        self::assertTrue($user->isConfirmed());
        self::assertNotEmpty($user->getConfirmationToken());
    }

    public function testSendWelcomeRegisteredByAdminEmail()
    {
        $user = new CustomerUser();

        $this->emailProcessor->expects(self::once())
            ->method('sendWelcomeForRegisteredByAdminNotification')
            ->with(self::identicalTo($user));

        $this->userManager->sendWelcomeRegisteredByAdminEmail($user);

        self::assertNotEmpty($user->getConfirmationToken());
    }

    public function testRegisterConfirmationRequiredNotFrontendRequest()
    {
        $user = new CustomerUser();
        $user->setEnabled(false);

        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(false);

        $this->websiteManager->expects(self::never())
            ->method('getCurrentWebsite');

        $this->configManager->expects(self::once())
            ->method('get')
            ->with('oro_customer.confirmation_required')
            ->willReturn(true);

        $em = $this->expectGetEntityManager();
        $em->expects(self::once())
            ->method('persist')
            ->with(self::identicalTo($user));
        $em->expects(self::once())
            ->method('flush');

        $this->emailProcessor->expects(self::once())
            ->method('sendConfirmationEmail')
            ->with(self::identicalTo($user));

        $this->userManager->register($user);

        self::assertFalse($user->isEnabled());
        self::assertNotEmpty($user->getConfirmationToken());
    }

    public function testRegisterConfirmationNotRequiredWhenWebsiteSettingsExist()
    {
        $defaultLocalizationCode = 'en';
        $currentLocalizationCode = 'fr_FR';

        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $website = new Website();
        $defaultLocalization = new Localization();
        $defaultLocalization->setFormattingCode($defaultLocalizationCode);
        $websiteSettings = new CustomerUserSettings($website);
        $websiteSettings->setLocalization($defaultLocalization);
        $user = new CustomerUser();
        $user->setWebsiteSettings($websiteSettings);
        $user->setConfirmed(false);

        $this->websiteManager->expects(self::once())
            ->method('getCurrentWebsite')
            ->willReturn($website);

        $currentLocalization = new Localization();
        $currentLocalization->setFormattingCode($currentLocalizationCode);
        $this->localizationHelper->expects(self::once())
            ->method('getCurrentLocalization')
            ->willReturn($currentLocalization);

        $this->configManager->expects(self::once())
            ->method('get')
            ->with('oro_customer.confirmation_required')
            ->willReturn(false);

        $this->emailProcessor->expects(self::once())
            ->method('sendWelcomeNotification')
            ->with($user);

        $expectedWebsiteSettings = new CustomerUserSettings($website);
        $expectedWebsiteSettings->setLocalization($currentLocalization);
        $expectedWebsiteSettings->setCustomerUser($user);

        $this->userManager->register($user);

        self::assertTrue($user->isConfirmed());
        self::assertEquals($expectedWebsiteSettings, $user->getWebsiteSettings($website));
    }

    public function testRegisterConfirmationNotRequiredWhenWebsiteSettingsNotExist()
    {
        $currentLocalizationCode = 'fr_FR';

        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $user = new CustomerUser();
        $user->setConfirmed(false);

        $website = new Website();
        $this->websiteManager->expects(self::once())
            ->method('getCurrentWebsite')
            ->willReturn($website);

        $currentLocalization = new Localization();
        $currentLocalization->setFormattingCode($currentLocalizationCode);
        $this->localizationHelper->expects(self::once())
            ->method('getCurrentLocalization')
            ->willReturn($currentLocalization);

        $this->configManager->expects(self::once())
            ->method('get')
            ->with('oro_customer.confirmation_required')
            ->willReturn(false);

        $this->emailProcessor->expects(self::once())
            ->method('sendWelcomeNotification')
            ->with($user);

        $expectedWebsiteSettings = new CustomerUserSettings($website);
        $expectedWebsiteSettings->setLocalization($currentLocalization);
        $expectedWebsiteSettings->setCustomerUser($user);

        $this->userManager->register($user);

        self::assertTrue($user->isConfirmed());
        self::assertEquals($expectedWebsiteSettings, $user->getWebsiteSettings($website));
    }

    public function testRegisterConfirmationNotRequired()
    {
        $user = new CustomerUser();
        $user->setConfirmed(false);

        $this->configManager->expects(self::once())
            ->method('get')
            ->with('oro_customer.confirmation_required')
            ->willReturn(false);

        $this->emailProcessor->expects(self::once())
            ->method('sendWelcomeNotification')
            ->with($user);

        $this->userManager->register($user);

        self::assertTrue($user->isConfirmed());
    }

    public function testSendResetPasswordEmail()
    {
        $user = new CustomerUser();
        $this->emailProcessor->expects(self::once())
            ->method('sendResetPasswordEmail')
            ->with($user);
        $this->userManager->sendResetPasswordEmail($user);
    }

    /**
     * @dataProvider isConfirmationRequiredDataProvider
     */
    public function testIsConfirmationRequired($value)
    {
        $this->configManager->expects(self::once())
            ->method('get')
            ->with('oro_customer.confirmation_required')
            ->willReturn($value);

        self::assertEquals($value, $this->userManager->isConfirmationRequired());
    }

    /**
     * @return array
     */
    public function isConfirmationRequiredDataProvider()
    {
        return [
            [true],
            [false]
        ];
    }

    public function testUpdateUserWithPlainPassword()
    {
        $password = 'password';
        $encodedPassword = 'encodedPassword';
        $salt = 'salt';

        $user = new CustomerUser();
        $user->setPlainPassword($password);
        $user->setSalt($salt);

        $encoder = $this->expectGetPasswordEncoder($user);
        $encoder->expects(self::once())
            ->method('encodePassword')
            ->with($password, $salt)
            ->willReturn($encodedPassword);

        $em = $this->expectGetEntityManager();
        $em->expects(self::once())
            ->method('persist')
            ->with(self::identicalTo($user));
        $em->expects(self::once())
            ->method('flush');

        $this->userManager->updateUser($user);

        self::assertNull($user->getPlainPassword());
        self::assertEquals($encodedPassword, $user->getPassword());
    }

    public function testFindUserBy()
    {
        $criteria = ['id' => 1];
        $user = $this->createMock(CustomerUser::class);

        $em = $this->expectGetEntityManager();
        $repository = $this->expectGetRepository($em);
        $repository->expects(self::once())
            ->method('findOneBy')
            ->with(array_merge($criteria, ['isGuest' => false]))
            ->willReturn($user);

        self::assertSame($user, $this->userManager->findUserBy($criteria));
    }
}
