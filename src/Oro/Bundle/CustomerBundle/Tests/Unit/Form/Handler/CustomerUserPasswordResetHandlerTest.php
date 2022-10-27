<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Handler;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\CustomerBundle\Form\Handler\CustomerUserPasswordResetHandler;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class CustomerUserPasswordResetHandlerTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|CustomerUserManager */
    private $userManager;

    /** @var CustomerUserPasswordResetHandler */
    private $handler;

    protected function setUp(): void
    {
        $this->userManager = $this->createMock(CustomerUserManager::class);

        $this->handler = new CustomerUserPasswordResetHandler($this->userManager);
    }

    public function testProcess()
    {
        $user = $this->createMock(CustomerUser::class);
        $user->expects($this->once())
            ->method('setConfirmationToken')
            ->with(null)
            ->willReturnSelf();
        $user->expects($this->once())
            ->method('setPasswordRequestedAt')
            ->with(null)
            ->willReturnSelf();
        $user->expects($this->once())
            ->method('setConfirmed')
            ->with(true)
            ->willReturnSelf();

        $request = $this->createMock(Request::class);
        $request->expects($this->once())
            ->method('isMethod')
            ->with('POST')
            ->willReturn(true);

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())
            ->method('getData')
            ->willReturn($user);
        $form->expects($this->once())
            ->method('handleRequest')
            ->with($request);
        $form->expects($this->once())
            ->method('isSubmitted')
            ->willReturn(true);
        $form->expects($this->once())
            ->method('isValid')
            ->willReturn(true);

        $this->userManager->expects($this->once())
            ->method('updateUser')
            ->with($user);

        $this->assertTrue($this->handler->process($form, $request));
    }
}
