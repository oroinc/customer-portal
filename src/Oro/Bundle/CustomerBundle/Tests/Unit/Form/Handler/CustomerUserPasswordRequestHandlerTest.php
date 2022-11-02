<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Handler;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\CustomerBundle\Form\Handler\CustomerUserPasswordRequestHandler;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

class CustomerUserPasswordRequestHandlerTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|CustomerUserManager */
    private $userManager;

    /** @var \PHPUnit\Framework\MockObject\MockObject|TranslatorInterface */
    private $translator;

    /** @var \PHPUnit\Framework\MockObject\MockObject|LoggerInterface */
    private $logger;

    /** @var \PHPUnit\Framework\MockObject\MockObject|FormInterface */
    private $form;

    /** @var \PHPUnit\Framework\MockObject\MockObject|Request */
    private $request;

    /** @var CustomerUserPasswordRequestHandler */
    private $handler;

    protected function setUp(): void
    {
        $this->userManager = $this->createMock(CustomerUserManager::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->handler = new CustomerUserPasswordRequestHandler(
            $this->userManager,
            $this->translator,
            $this->logger
        );

        $this->form = $this->createMock(FormInterface::class);
        $this->request = $this->createMock(Request::class);
    }

    public function testProcessInvalidUser()
    {
        $email = 'test@test.com';

        $this->assertValidFormCall($email);

        $this->userManager->expects($this->once())
            ->method('findUserByUsernameOrEmail')
            ->with($email)
            ->willReturn(null);

        $this->userManager->expects($this->never())
            ->method('sendResetPasswordEmail');
        $this->userManager->expects($this->never())
            ->method('updateUser');

        $this->assertEquals($email, $this->handler->process($this->form, $this->request));
    }

    public function testProcessEmailSendFail()
    {
        $email = 'test@test.com';
        $exception = new \Exception();

        $user = $this->createMock(CustomerUser::class);

        $this->assertValidFormCall($email);

        $this->userManager->expects($this->once())
            ->method('findUserByUsernameOrEmail')
            ->with($email)
            ->willReturn($user);

        $this->userManager->expects($this->once())
            ->method('sendResetPasswordEmail')
            ->with($user)
            ->willThrowException($exception);

        $this->assertFormErrorAdded(
            $this->form,
            'oro.email.handler.unable_to_send_email'
        );
        $this->logger->expects($this->once())
            ->method('error')
            ->with(
                'Unable to sent the reset password email.',
                ['email' => $email, 'exception' => $exception]
            );

        $this->assertNull($this->handler->process($this->form, $this->request));
    }

    public function testProcess()
    {
        $email = 'test@test.com';

        $user = $this->createMock(CustomerUser::class);

        $this->assertValidFormCall($email);

        $this->userManager->expects($this->once())
            ->method('findUserByUsernameOrEmail')
            ->with($email)
            ->willReturn($user);

        $this->userManager->expects($this->once())
            ->method('sendResetPasswordEmail')
            ->with($user);

        $this->userManager->expects($this->once())
            ->method('updateUser')
            ->with($user);

        $this->assertEquals($email, $this->handler->process($this->form, $this->request));
    }

    /**
     * @param \PHPUnit\Framework\MockObject\MockObject|FormInterface $form
     * @param string $message
     */
    public function assertFormErrorAdded($form, $message)
    {
        $this->translator->expects($this->once())
            ->method('trans')
            ->with($message)
            ->willReturn($message);

        $form->expects($this->once())
            ->method('addError')
            ->with(new FormError($message));
    }

    /**
     * @param string $email
     */
    private function assertValidFormCall($email)
    {
        $this->request->expects($this->once())
            ->method('isMethod')
            ->with('POST')
            ->willReturn(true);

        $this->form->expects($this->once())
            ->method('handleRequest')
            ->with($this->request);
        $this->form->expects($this->once())
            ->method('isSubmitted')
            ->willReturn(true);
        $this->form->expects($this->once())
            ->method('isValid')
            ->willReturn(true);

        $emailSubform = $this->createMock(FormInterface::class);
        $emailSubform->expects($this->once())
            ->method('getData')
            ->willReturn($email);

        $this->form->expects($this->once())
            ->method('get')
            ->with('email')
            ->willReturn($emailSubform);

        return $emailSubform;
    }
}
