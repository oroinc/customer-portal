<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Handler;

use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\CustomerBundle\Form\Handler\CustomerUserPasswordResetHandler;
use Oro\Bundle\CustomerBundle\Tests\Unit\Stub\CustomerUserStub;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class CustomerUserPasswordResetHandlerTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|CustomerUserManager */
    private $userManager;

    private $logger;

    /** @var CustomerUserPasswordResetHandler */
    private $handler;

    protected function setUp(): void
    {
        $this->userManager = $this->createMock(CustomerUserManager::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->handler = new CustomerUserPasswordResetHandler($this->userManager, $this->logger);
    }

    public function testProcess(): void
    {
        $customerUser = new CustomerUserStub(12);
        $customerUser->setConfirmationToken('test');
        $customerUser->setPasswordRequestedAt(new \DateTime('2022-01-01'));

        $request = $this->createMock(Request::class);
        $request->expects(self::once())
            ->method('isMethod')
            ->with('POST')
            ->willReturn(true);

        $form = $this->createMock(FormInterface::class);
        $form->expects(self::once())
            ->method('getData')
            ->willReturn($customerUser);
        $form->expects(self::once())
            ->method('handleRequest')
            ->with($request);
        $form->expects(self::once())
            ->method('isSubmitted')
            ->willReturn(true);
        $form->expects(self::once())
            ->method('isValid')
            ->willReturn(true);

        $this->logger->expects(self::once())
            ->method('notice')
            ->with(
                'Password was successfully reset for customer user.',
                ['user_id' => 12]
            );

        $this->userManager->expects(self::once())
            ->method('updateUser')
            ->with($customerUser);
        $this->userManager->expects(self::once())
            ->method('setAuthStatus')
            ->with($customerUser, CustomerUserManager::STATUS_ACTIVE);

        self::assertTrue($this->handler->process($form, $request));
        self::assertNull($customerUser->getConfirmationToken());
        self::assertNull($customerUser->getPasswordRequestedAt());
        self::assertTrue($customerUser->isConfirmed());
    }

    public function testProcessWithNotValidForm(): void
    {
        $customerUser = new CustomerUserStub(40);
        $customerUser->setConfirmationToken('test');
        $customerUser->setPasswordRequestedAt(new \DateTime('2022-01-01'));
        $customerUser->setConfirmed(false);

        $request = $this->createMock(Request::class);
        $request->expects(self::once())
            ->method('isMethod')
            ->with('POST')
            ->willReturn(true);

        $form = $this->createMock(FormInterface::class);
        $form->expects(self::once())
            ->method('getData')
            ->willReturn($customerUser);
        $form->expects(self::once())
            ->method('handleRequest')
            ->with($request);
        $form->expects(self::once())
            ->method('isSubmitted')
            ->willReturn(true);
        $form->expects(self::once())
            ->method('isValid')
            ->willReturn(false);

        $this->logger->expects(self::once())
            ->method('notice')
            ->with(
                'Password reset for customer user was failed.',
                ['user_id' => 40]
            );

        $this->userManager->expects(self::never())
            ->method('updateUser');
        $this->userManager->expects(self::never())
            ->method('setAuthStatus');

        self::assertFalse($this->handler->process($form, $request));
        self::assertNotNull($customerUser->getConfirmationToken());
        self::assertNotNull($customerUser->getPasswordRequestedAt());
        self::assertFalse($customerUser->isConfirmed());
    }
}
