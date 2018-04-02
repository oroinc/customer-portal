<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Mailer;

use Oro\Bundle\CustomerBundle\Event\CustomerUserEmailSendEvent;
use Oro\Bundle\UserBundle\Tests\Unit\Mailer\AbstractProcessorTest;
use Oro\Bundle\CustomerBundle\Mailer\Processor;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Mailer\Processor;
use Oro\Bundle\UserBundle\Tests\Unit\Mailer\AbstractProcessorTest;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ProcessorTest extends AbstractProcessorTest
{
    const PASSWORD = '123456';

    /**
     * @var Processor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mailProcessor;

    /**
     * @var CustomerUser
     */
    protected $user;

    /**
     * @var EventDispatcherInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventDispatcher;

    protected function setUp()
    {
        parent::setUp();

        $this->user = new CustomerUser();
        $this->user
            ->setEmail('email_to@example.com')
            ->setPlainPassword(self::PASSWORD)
            ->setConfirmationToken($this->user->generateToken());

        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->mailProcessor = new Processor(
            $this->managerRegistry,
            $this->configManager,
            $this->renderer,
            $this->emailHolderHelper,
            $this->mailer,
            $this->eventDispatcher
        );
    }

    protected function tearDown()
    {
        parent::tearDown();

        unset($this->user);
    }

    /**
     * @param string $template
     * @param array $params
     */
    private function assertEventDispatched($template, array $params)
    {
        $event = new CustomerUserEmailSendEvent($this->user, $template, $params);
        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with(CustomerUserEmailSendEvent::NAME, $event);
    }

    public function testSendWelcomeNotification()
    {
        $this->assertSendCalled(
            Processor::WELCOME_EMAIL_TEMPLATE_NAME,
            ['entity' => $this->user, 'password' => self::PASSWORD],
            $this->buildMessage($this->user->getEmail())
        );

        $this->assertEventDispatched(
            Processor::WELCOME_EMAIL_TEMPLATE_NAME,
            ['entity' => $this->user, 'password' => self::PASSWORD]
        );

        $this->mailProcessor->sendWelcomeNotification($this->user, self::PASSWORD);
    }

    public function testSendConfirmationEmail()
    {
        $this->assertSendCalled(
            Processor::CONFIRMATION_EMAIL_TEMPLATE_NAME,
            ['entity' => $this->user, 'token' => $this->user->getConfirmationToken()],
            $this->buildMessage($this->user->getEmail())
        );

        $this->assertEventDispatched(
            Processor::CONFIRMATION_EMAIL_TEMPLATE_NAME,
            ['entity' => $this->user, 'token' => $this->user->getConfirmationToken()]
        );

        $this->mailProcessor->sendConfirmationEmail($this->user);
    }

    public function testSendResetPasswordEmail()
    {
        $this->assertSendCalled(
            Processor::RESET_PASSWORD_EMAIL_TEMPLATE_NAME,
            ['entity' => $this->user],
            $this->buildMessage($this->user->getEmail())
        );

        $this->assertEventDispatched(
            Processor::RESET_PASSWORD_EMAIL_TEMPLATE_NAME,
            ['entity' => $this->user]
        );

        $this->mailProcessor->sendResetPasswordEmail($this->user);
    }
}
