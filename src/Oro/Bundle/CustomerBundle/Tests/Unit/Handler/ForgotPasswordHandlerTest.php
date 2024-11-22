<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Handler;

use Oro\Bundle\CheckoutBundle\Handler\CheckoutHandlerInterface;
use Oro\Bundle\CustomerBundle\Form\Handler\CustomerUserPasswordRequestHandler;
use Oro\Bundle\CustomerBundle\Handler\ForgotPasswordHandler;
use Oro\Bundle\CustomerBundle\Layout\DataProvider\FrontendCustomerUserFormProvider;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ForgotPasswordHandlerTest extends TestCase
{
    private CheckoutHandlerInterface $innerHandler;
    private ForgotPasswordHandler $forgotPasswordHandler;

    protected function setUp(): void
    {
        $this->innerHandler = $this->createMock(CheckoutHandlerInterface::class);

        $this->forgotPasswordHandler = new ForgotPasswordHandler(
            $this->createMock(CustomerUserPasswordRequestHandler::class),
            $this->createMock(FrontendCustomerUserFormProvider::class),
            $this->createMock(RequestStack::class)
        );
        $this->forgotPasswordHandler->setCheckoutHandler($this->innerHandler);
    }

    public function testHandle()
    {
        $request = new Request();
        $request->query->set('isCheckEmail', true);

        $this->innerHandler->expects($this->once())
            ->method('handle')
            ->with($this->isInstanceOf(WorkflowItem::class), $request);

        $this->assertTrue($this->forgotPasswordHandler->handle($request));
    }

    public function testIsSupported()
    {
        $request = $this->createMock(Request::class);
        $this->innerHandler->expects($this->once())
            ->method('isSupported')
            ->with($request)
            ->willReturn(true);

        $this->assertTrue($this->forgotPasswordHandler->isForgotPasswordRequest($request));
    }
}
