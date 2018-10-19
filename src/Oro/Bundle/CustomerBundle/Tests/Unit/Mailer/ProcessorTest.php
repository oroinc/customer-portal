<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Mailer;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Event\CustomerUserEmailSendEvent;
use Oro\Bundle\CustomerBundle\Mailer\Processor;
use Oro\Bundle\UserBundle\Mailer\UserTemplateEmailSender;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ProcessorTest extends \PHPUnit\Framework\TestCase
{
    const PASSWORD = '123456';

    /**
     * @var CustomerUser
     */
    private $user;

    /**
     * @var EventDispatcherInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $eventDispatcher;

    /**
     * @var UserTemplateEmailSender|MockObject
     */
    private $userTemplateEmailSender;

    /**
     * @var Processor
     */
    private $mailProcessor;

    protected function setUp()
    {
        $this->user = new CustomerUser();
        $website = new Website();
        $this->user
            ->setEmail('email_to@example.com')
            ->setWebsite($website)
            ->setPlainPassword(self::PASSWORD)
            ->setConfirmationToken($this->user->generateToken());

        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->userTemplateEmailSender = $this->createMock(UserTemplateEmailSender::class);
        $this->mailProcessor = new Processor($this->userTemplateEmailSender, $this->eventDispatcher);
    }

    public function testSendWelcomeNotification(): void
    {
        $returnValue = 1;
        $this->userTemplateEmailSender
            ->expects($this->once())
            ->method('sendUserTemplateEmail')
            ->with(
                $this->user,
                Processor::WELCOME_EMAIL_TEMPLATE_NAME,
                ['entity' => $this->user],
                $this->user->getWebsite()
            )
            ->willReturn($returnValue);

        $this->assertEventDispatched(
            Processor::WELCOME_EMAIL_TEMPLATE_NAME,
            ['entity' => $this->user]
        );

        self::assertEquals($returnValue, $this->mailProcessor->sendWelcomeNotification($this->user));
    }

    public function testSendWelcomeForRegisteredByAdminNotification(): void
    {
        $returnValue = 1;

        $this->userTemplateEmailSender
            ->expects($this->once())
            ->method('sendUserTemplateEmail')
            ->with(
                $this->user,
                Processor::WELCOME_EMAIL_REGISTERED_BY_ADMIN_TEMPLATE_NAME,
                ['entity' => $this->user],
                $this->user->getWebsite()
            )
            ->willReturn($returnValue);

        $this->assertEventDispatched(
            Processor::WELCOME_EMAIL_REGISTERED_BY_ADMIN_TEMPLATE_NAME,
            ['entity' => $this->user]
        );

        self::assertEquals(
            $returnValue,
            $this->mailProcessor->sendWelcomeForRegisteredByAdminNotification($this->user)
        );
    }

    public function testSendConfirmationEmail(): void
    {
        $returnValue = 1;
        $this->userTemplateEmailSender
            ->expects($this->once())
            ->method('sendUserTemplateEmail')
            ->with(
                $this->user,
                Processor::CONFIRMATION_EMAIL_TEMPLATE_NAME,
                ['entity' => $this->user, 'token' => $this->user->getConfirmationToken()],
                $this->user->getWebsite()
            )
            ->willReturn($returnValue);

        $this->assertEventDispatched(
            Processor::CONFIRMATION_EMAIL_TEMPLATE_NAME,
            ['entity' => $this->user, 'token' => $this->user->getConfirmationToken()]
        );

        self::assertEquals($returnValue, $this->mailProcessor->sendConfirmationEmail($this->user));
    }

    public function testSendResetPasswordEmail(): void
    {
        $returnValue = 1;
        $this->userTemplateEmailSender
            ->expects($this->once())
            ->method('sendUserTemplateEmail')
            ->with(
                $this->user,
                Processor::RESET_PASSWORD_EMAIL_TEMPLATE_NAME,
                ['entity' => $this->user],
                $this->user->getWebsite()
            )
            ->willReturn($returnValue);

        $this->assertEventDispatched(
            Processor::RESET_PASSWORD_EMAIL_TEMPLATE_NAME,
            ['entity' => $this->user]
        );

        self::assertEquals($returnValue, $this->mailProcessor->sendResetPasswordEmail($this->user));
    }

    /**
     * @param string $template
     * @param array $params
     */
    private function assertEventDispatched($template, array $params): void
    {
        $event = new CustomerUserEmailSendEvent($this->user, $template, $params);
        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with(CustomerUserEmailSendEvent::NAME, $event);
    }
}
