<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Entity;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\UserBundle\Entity\Repository\AbstractUserRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class CustomerUserManagerTest extends \PHPUnit_Framework_TestCase
{
    const USER_CLASS = 'Oro\Bundle\CustomerBundle\Entity\CustomerUser';

    /**
     * @var CustomerUserManager
     */
    protected $userManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $om;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $ef;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $emailProcessor;

    protected function setUp()
    {
        $this->ef = $this->createMock('Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface');
        $this->om = $this->createMock('Doctrine\Common\Persistence\ObjectManager');
        $this->registry = $this->createMock('Doctrine\Common\Persistence\ManagerRegistry');

        $this->configManager = $this->getMockBuilder('Oro\Bundle\ConfigBundle\Config\ConfigManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->emailProcessor = $this->getMockBuilder('Oro\Bundle\CustomerBundle\Mailer\Processor')
            ->disableOriginalConstructor()
            ->getMock();

        $container = $this->createMock('Symfony\Component\DependencyInjection\ContainerInterface');
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
                        ]
                    ]
                )
            );

        $this->userManager = new CustomerUserManager(static::USER_CLASS, $this->registry, $this->ef);
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
            ->with($user, false);

        $this->userManager->confirmRegistration($user);

        $this->assertTrue($user->isConfirmed());
    }

    /**
     * @dataProvider welcomeEmailDataProvider
     *
     * @param bool $sendPassword
     */
    public function testSendWelcomeEmail($sendPassword)
    {
        $password = 'test';

        $user = new CustomerUser();
        $user->setPlainPassword($password);

        $this->emailProcessor->expects($this->once())
            ->method('sendWelcomeNotification')
            ->with($user, $sendPassword ? $password : null);

        $this->configManager->expects($this->once())
            ->method('get')
            ->with('oro_customer.send_password_in_welcome_email')
            ->willReturn($sendPassword);

        $this->userManager->sendWelcomeEmail($user);
    }

    /**
     * @return array
     */
    public function welcomeEmailDataProvider()
    {
        return [
            ['sendPassword' => true],
            ['sendPassword' => false]
        ];
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

    public function testRegisterConfirmationRequired()
    {
        $password = 'test1Q';

        $user = new CustomerUser();
        $user->setEnabled(false);
        $user->setPlainPassword($password);

        $this->configManager->expects($this->once())
            ->method('get')
            ->with('oro_customer.confirmation_required')
            ->will($this->returnValue(true));

        $this->emailProcessor->expects($this->once())
            ->method('sendConfirmationEmail')
            ->with($user);

        $this->userManager->register($user);

        $this->assertFalse($user->isEnabled());
        $this->assertNotEmpty($user->getConfirmationToken());
    }

    public function testRegisterConfirmationNotRequired()
    {
        $password = 'test1Q';

        $user = new CustomerUser();
        $user->setConfirmed(false);
        $user->setPlainPassword($password);

        $this->configManager->expects($this->exactly(2))
            ->method('get')
            ->willReturnMap(
                [
                    ['oro_customer.confirmation_required', false, false, null, false],
                    ['oro_customer.send_password_in_welcome_email', false, false, null, true]
                ]
            );

        $this->emailProcessor->expects($this->once())
            ->method('sendWelcomeNotification')
            ->with($user, $password);

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

    /**
     * @param CustomerUser $user
     */
    private function assertRepositoryCalled(CustomerUser $user)
    {
        $this->registry
            ->expects(self::once())
            ->method('getManagerForClass')
            ->willReturn($this->om);

        $this->om
            ->expects(self::once())
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
