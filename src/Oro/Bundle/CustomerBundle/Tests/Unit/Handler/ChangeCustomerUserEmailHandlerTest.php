<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Handler;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\CustomerBundle\Handler\ChangeCustomerUserEmailHandler;
use Oro\Bundle\CustomerBundle\Model\EmailHolder;
use Oro\Bundle\CustomerBundle\Tests\Unit\Fixtures\Entity\User;
use Oro\Bundle\EmailBundle\Model\EmailTemplateCriteria;
use Oro\Bundle\NotificationBundle\Manager\EmailNotificationManager;
use Oro\Bundle\NotificationBundle\Model\TemplateEmailNotification;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ChangeCustomerUserEmailHandlerTest extends TestCase
{
    private EmailNotificationManager&MockObject $emailNotificationManager;
    private CustomerUserManager&MockObject $customerUserManager;
    private LoggerInterface&MockObject $logger;
    private ChangeCustomerUserEmailHandler $handler;

    #[\Override]
    protected function setUp(): void
    {
        $this->emailNotificationManager = $this->createMock(EmailNotificationManager::class);
        $this->customerUserManager = $this->createMock(CustomerUserManager::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->handler = new ChangeCustomerUserEmailHandler(
            $this->emailNotificationManager,
            $this->customerUserManager,
            $this->logger
        );
    }

    public function testInitializeEmailChangeAndSendToOldEmail(): void
    {
        $customerUser = new User();
        $customerUser->setEmail('old@example.com');
        $customerUser->setNewEmail('new@example.com');

        $this->customerUserManager->expects(self::once())
            ->method('updateUser')
            ->with($customerUser);

        $this->emailNotificationManager->expects(self::once())
            ->method('processSingle')
            ->willReturnCallback(function (TemplateEmailNotification $notification) use ($customerUser) {
                self::assertSame($customerUser, $notification->getEntity());
                self::assertEquals(
                    new EmailTemplateCriteria(
                        'customer_user_email_change_verification_to_old_email',
                        CustomerUser::class
                    ),
                    $notification->getTemplateCriteria()
                );
                self::assertSame([$customerUser], $notification->getRecipients());
            });

        $this->handler->initializeEmailChangeAndSendToOldEmail($customerUser);

        self::assertNotEmpty($customerUser->getNewEmailVerificationCode());
        self::assertInstanceOf(\DateTimeInterface::class, $customerUser->getEmailVerificationCodeRequestedAt());
    }

    public function testSendEmailToNewEmail(): void
    {
        $customerUser = new User();
        $customerUser->setEmail('old@example.com');
        $customerUser->setNewEmail('new@example.com');

        $this->customerUserManager->expects(self::never())
            ->method('updateUser');

        $this->emailNotificationManager->expects(self::once())
            ->method('processSingle')
            ->willReturnCallback(function (TemplateEmailNotification $notification) use ($customerUser) {
                self::assertSame($customerUser, $notification->getEntity());
                self::assertEquals(
                    new EmailTemplateCriteria('customer_user_email_change_confirmation', CustomerUser::class),
                    $notification->getTemplateCriteria()
                );
                self::assertCount(1, $notification->getRecipients());
                self::assertEquals(
                    [new EmailHolder('new@example.com')],
                    $notification->getRecipients()
                );
            });

        self::assertTrue($this->handler->sendEmailToNewEmail($customerUser));
    }

    public function testSendEmailToNewEmailWhenNotificationFails(): void
    {
        $customerUser = new User();
        $customerUser->setNewEmail('new@example.com');

        $exception = new \RuntimeException('send failed');

        $this->customerUserManager->expects(self::never())
            ->method('updateUser');
        $this->emailNotificationManager->expects(self::once())
            ->method('processSingle')
            ->willThrowException($exception);
        $this->logger->expects(self::once())
            ->method('error')
            ->with(
                'Sending email to new@example.com failed.',
                ['exception' => $exception]
            );

        self::assertFalse($this->handler->sendEmailToNewEmail($customerUser));
    }

    public function testConfirmNewEmail(): void
    {
        $requestedAt = new \DateTimeImmutable('2024-01-01 00:00:00', new \DateTimeZone('UTC'));
        $customerUser = new User();
        $customerUser->setEmail('old@example.com');
        $customerUser->setNewEmail('new@example.com');
        $customerUser->setNewEmailVerificationCode('verification-code');
        $customerUser->setEmailVerificationCodeRequestedAt($requestedAt);

        $this->customerUserManager->expects(self::once())
            ->method('updateUser')
            ->with($customerUser);

        $this->emailNotificationManager->expects(self::once())
            ->method('processSingle')
            ->willReturnCallback(function (TemplateEmailNotification $notification) use ($customerUser) {
                self::assertSame($customerUser, $notification->getEntity());
                self::assertEquals(
                    new EmailTemplateCriteria(
                        'customer_user_email_change_verification_to_new_email',
                        CustomerUser::class
                    ),
                    $notification->getTemplateCriteria()
                );
                self::assertEquals(
                    [new EmailHolder('old@example.com')],
                    $notification->getRecipients()
                );
            });

        $this->handler->confirmNewEmail($customerUser);
        self::assertSame('new@example.com', $customerUser->getEmail());
        self::assertNull($customerUser->getNewEmail());
        self::assertNull($customerUser->getNewEmailVerificationCode());
        self::assertNull($customerUser->getEmailVerificationCodeRequestedAt());
    }

    public function testCancelEmailChange(): void
    {
        $customerUser = new User();
        $customerUser->setNewEmail('new@example.com');
        $customerUser->setNewEmailVerificationCode('verification-code');
        $customerUser->setEmailVerificationCodeRequestedAt(
            new \DateTimeImmutable('2024-01-01 00:00:00', new \DateTimeZone('UTC'))
        );

        $this->customerUserManager->expects(self::once())
            ->method('updateUser')
            ->with($customerUser);

        $this->handler->cancelEmailChange($customerUser);

        self::assertNull($customerUser->getNewEmail());
        self::assertNull($customerUser->getNewEmailVerificationCode());
        self::assertNull($customerUser->getEmailVerificationCodeRequestedAt());
    }
}
