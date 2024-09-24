<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Handler;

use Oro\Bundle\CustomerBundle\Form\Handler\CustomerUserPasswordRequestHandler;
use Oro\Bundle\CustomerBundle\Handler\ForgotPasswordHandler;
use Oro\Bundle\CustomerBundle\Layout\DataProvider\FrontendCustomerUserFormProvider;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;

class ForgotPasswordHandlerTest extends TestCase
{
    /**
     * @var CustomerUserPasswordRequestHandler|MockObject
     */
    private $passwordRequestHandler;

    /**
     * @var FrontendCustomerUserFormProvider|MockObject
     */
    private $customerUserFormProvider;

    /**
     * @var Session|MockObject
     */
    private $session;

    /**
     * @var RequestStack|MockObject
     */
    private $requestStack;

    /**
     * @var ForgotPasswordHandler
     */
    private $forgotPasswordHandler;

    #[\Override]
    protected function setUp(): void
    {
        $this->passwordRequestHandler = $this->createMock(CustomerUserPasswordRequestHandler::class);
        $this->customerUserFormProvider = $this->createMock(FrontendCustomerUserFormProvider::class);
        $this->session = $this->createMock(Session::class);
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->requestStack->expects($this->any())
            ->method('getSession')
            ->willReturn($this->session);
        $this->forgotPasswordHandler = new ForgotPasswordHandler(
            $this->passwordRequestHandler,
            $this->customerUserFormProvider,
            $this->requestStack
        );
    }

    public function testHandleWithGetMethod()
    {
        $request = new Request();
        $workflowItem = $this->createMock(WorkflowItem::class);
        $request->setMethod(Request::METHOD_GET);

        $this->customerUserFormProvider->expects($this->never())
            ->method($this->anything());

        $this->forgotPasswordHandler->handle($workflowItem, $request);
    }

    public function testHandleWithoutParameter()
    {
        $request = new Request();
        $workflowItem = $this->createMock(WorkflowItem::class);
        $request->setMethod(Request::METHOD_POST);
        $this->customerUserFormProvider->expects($this->never())
            ->method($this->anything());

        $this->forgotPasswordHandler->handle($workflowItem, $request);
    }

    public function testHandleWithoutUser()
    {
        $request = new Request();
        $workflowItem = $this->createMock(WorkflowItem::class);
        $request->setMethod(Request::METHOD_POST);
        $request->query->add(['isForgotPassword' => true]);

        $form = $this->createMock(FormInterface::class);
        $this->customerUserFormProvider->expects($this->once())
            ->method('getForgotPasswordForm')
            ->willReturn($form);

        $this->passwordRequestHandler->expects($this->once())
            ->method('process')
            ->with($form, $request)
            ->willReturn(null);

        $this->requestStack->expects($this->never())
            ->method('getSession');

        $this->forgotPasswordHandler->handle($workflowItem, $request);
    }

    public function testHandleProcess()
    {
        $request = new Request();
        $workflowItem = $this->createMock(WorkflowItem::class);
        $request->setMethod(Request::METHOD_POST);
        $request->query->add(['isForgotPassword' => true]);

        $form = $this->createMock(FormInterface::class);
        $this->customerUserFormProvider->expects($this->once())
            ->method('getForgotPasswordForm')
            ->willReturn($form);

        $this->passwordRequestHandler->expects($this->once())
            ->method('process')
            ->with($form, $request)
            ->willReturn('test@example.org');

        $this->session->expects($this->once())
            ->method('set')
            ->with(
                'oro_customer_user_reset_email',
                'test@example.org'
            );

        $this->forgotPasswordHandler->handle($workflowItem, $request);
        $this->assertNull($request->query->get('isForgotPassword'));
        $this->assertTrue($request->query->get('isCheckEmail'));
    }
}
