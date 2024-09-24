<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Handler;

use Oro\Bundle\CheckoutBundle\Handler\CheckoutHandlerInterface;
use Oro\Bundle\CustomerBundle\Handler\CustomerRegistrationCheckoutHandler;
use Oro\Bundle\CustomerBundle\Handler\CustomerRegistrationHandlerInterface;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class CustomerRegistrationCheckoutHandlerTest extends TestCase
{
    private CustomerRegistrationHandlerInterface|MockObject $registrationHandler;
    private CheckoutHandlerInterface|MockObject $checkoutGetHandler;

    private CustomerRegistrationCheckoutHandler $handler;

    #[\Override]
    protected function setUp(): void
    {
        $this->registrationHandler = $this->createMock(CustomerRegistrationHandlerInterface::class);
        $this->checkoutGetHandler = $this->createMock(CheckoutHandlerInterface::class);

        $this->handler = new CustomerRegistrationCheckoutHandler(
            $this->registrationHandler,
            $this->checkoutGetHandler
        );
    }

    public function testIsSupportedWithPostMethodAndRegistrationRequest(): void
    {
        $request = new Request([], [], [], [], [], ['REQUEST_METHOD' => Request::METHOD_POST]);

        $this->registrationHandler->expects($this->once())
            ->method('isRegistrationRequest')
            ->with($request)
            ->willReturn(true);

        $this->assertTrue($this->handler->isSupported($request));
    }

    public function testIsSupportedWithPostMethodAndNotRegistrationRequest(): void
    {
        $request = new Request([], [], [], [], [], ['REQUEST_METHOD' => Request::METHOD_POST]);

        $this->registrationHandler->expects($this->once())
            ->method('isRegistrationRequest')
            ->with($request)
            ->willReturn(false);

        $this->assertFalse($this->handler->isSupported($request));
    }

    public function testIsSupportedWithNonPostMethod(): void
    {
        $request = new Request([], [], [], [], [], ['REQUEST_METHOD' => Request::METHOD_GET]);

        $this->registrationHandler->expects($this->never())
            ->method('isRegistrationRequest');

        $this->assertFalse($this->handler->isSupported($request));
    }

    public function testHandleWithValidForm(): void
    {
        $workflowItem = new WorkflowItem();
        $request = new Request();
        $form = $this->createMock(FormInterface::class);

        $this->registrationHandler->expects($this->once())
            ->method('handleRegistration')
            ->with($request);

        $this->registrationHandler->expects($this->once())
            ->method('getForm')
            ->willReturn($form);

        $form->expects($this->once())
            ->method('isSubmitted')
            ->willReturn(true);

        $form->expects($this->once())
            ->method('isValid')
            ->willReturn(true);

        $this->checkoutGetHandler->expects($this->once())
            ->method('handle')
            ->with($workflowItem, $request);

        $this->handler->handle($workflowItem, $request);
    }

    public function testHandleWithInvalidForm(): void
    {
        $workflowItem = new WorkflowItem();
        $request = new Request();
        $form = $this->createMock(FormInterface::class);

        $this->registrationHandler->expects($this->once())
            ->method('handleRegistration')
            ->with($request);

        $this->registrationHandler->expects($this->once())
            ->method('getForm')
            ->willReturn($form);

        $form->expects($this->once())
            ->method('isSubmitted')
            ->willReturn(true);

        $form->expects($this->once())
            ->method('isValid')
            ->willReturn(false);

        $this->checkoutGetHandler->expects($this->never())
            ->method('handle');

        $this->handler->handle($workflowItem, $request);
    }
}
