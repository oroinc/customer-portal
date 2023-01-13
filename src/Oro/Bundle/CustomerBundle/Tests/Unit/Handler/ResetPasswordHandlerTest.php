<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Handler;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\CustomerBundle\Handler\ResetPasswordHandler;
use Oro\Bundle\EmailBundle\Model\EmailTemplateCriteria;
use Oro\Bundle\NotificationBundle\Manager\EmailNotificationManager;
use Oro\Bundle\NotificationBundle\Model\TemplateEmailNotification;
use Psr\Log\LoggerInterface;

class ResetPasswordHandlerTest extends \PHPUnit\Framework\TestCase
{
    /** @var EmailNotificationManager|\PHPUnit\Framework\MockObject\MockObject */
    private $emailNotificationManager;

    /** @var CustomerUserManager|\PHPUnit\Framework\MockObject\MockObject */
    private $customerUserManager;

    /** @var LoggerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $logger;

    /** @var ResetPasswordHandler */
    private $handler;

    protected function setUp(): void
    {
        $this->emailNotificationManager = $this->createMock(EmailNotificationManager::class);
        $this->customerUserManager = $this->createMock(CustomerUserManager::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->handler = new ResetPasswordHandler(
            $this->emailNotificationManager,
            $this->customerUserManager,
            $this->logger
        );
    }

    public function testResetPasswordAndNotifyIfUserDisabled()
    {
        $customerUser = new CustomerUser();
        $customerUser->setEnabled(false);
        $this->customerUserManager->expects(self::never())
            ->method('setAuthStatus');
        $this->customerUserManager->expects(self::never())
            ->method('updateUser');
        $this->emailNotificationManager->expects(self::never())
            ->method('processSingle');

        $result = $this->handler->resetPasswordAndNotify($customerUser);
        self::assertFalse($result);
    }

    public function testResetPasswordAndNotifyIfThrowsException()
    {
        $email = 'example@test.com';
        $customerUser = new CustomerUser();
        $customerUser->setEmail($email);
        $this->customerUserManager->expects(self::once())
            ->method('setAuthStatus')
            ->with($customerUser, CustomerUserManager::STATUS_RESET);
        $this->customerUserManager->expects(self::once())
            ->method('updateUser')
            ->with($customerUser);
        $exception = new \Exception();
        $this->emailNotificationManager->expects(self::once())
            ->method('processSingle')
            ->willThrowException($exception);
        $this->logger->expects(self::once())
            ->method('error')
            ->with(
                sprintf('Sending email to %s failed.', $email),
                ['exception' => $exception]
            );

        $result = $this->handler->resetPasswordAndNotify($customerUser);
        self::assertFalse($result);
    }

    public function testResetPasswordAndNotifyWhenNoConfirmationToken()
    {
        $email = 'example@test.com';
        $customerUser = new CustomerUser();
        $customerUser->setEmail($email);
        $this->customerUserManager->expects(self::once())
            ->method('setAuthStatus')
            ->with($customerUser, CustomerUserManager::STATUS_RESET);
        $this->customerUserManager->expects(self::once())
            ->method('updateUser')
            ->with($customerUser);
        $this->emailNotificationManager->expects(self::once())
            ->method('processSingle')
            ->willReturnCallback(
                function (TemplateEmailNotification $notification) use ($customerUser) {
                    self::assertSame($customerUser, $notification->getEntity());
                    self::assertInstanceOf(TemplateEmailNotification::class, $notification);
                    self::assertEquals(
                        new EmailTemplateCriteria('customer_user_force_reset_password', CustomerUser::class),
                        $notification->getTemplateCriteria()
                    );
                    self::assertEquals([$customerUser], $notification->getRecipients());
                }
            );

        $this->assertEmpty($customerUser->getConfirmationToken());
        $result = $this->handler->resetPasswordAndNotify($customerUser);
        self::assertTrue($result);
        self::assertNotEmpty($customerUser->getConfirmationToken());
    }

    public function testResetPasswordAndNotify()
    {
        $email = 'example@test.com';
        $token = 'sometoken';
        $customerUser = new CustomerUser();
        $customerUser->setConfirmationToken($token);
        $customerUser->setEmail($email);
        $this->customerUserManager->expects(self::once())
            ->method('setAuthStatus')
            ->with($customerUser, CustomerUserManager::STATUS_RESET);
        $this->customerUserManager->expects(self::once())
            ->method('updateUser')
            ->with($customerUser);

        $expectedNotification = new TemplateEmailNotification(
            new EmailTemplateCriteria('customer_user_force_reset_password', CustomerUser::class),
            [$customerUser],
            $customerUser
        );
        $this->emailNotificationManager->expects(self::once())
            ->method('processSingle')
            ->with($expectedNotification, [], $this->logger);

        $result = $this->handler->resetPasswordAndNotify($customerUser);
        self::assertTrue($result);
        self::assertEquals($token, $customerUser->getConfirmationToken());
    }
}
