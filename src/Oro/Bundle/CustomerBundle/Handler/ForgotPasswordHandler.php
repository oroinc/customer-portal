<?php

namespace Oro\Bundle\CustomerBundle\Handler;

use Oro\Bundle\CheckoutBundle\Handler\CheckoutHandlerInterface;
use Oro\Bundle\CustomerBundle\Form\Handler\CustomerUserPasswordRequestHandler;
use Oro\Bundle\CustomerBundle\Layout\DataProvider\FrontendCustomerUserFormProvider;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Handling forgot password request during checkout
 * @deprecated replaced with ForgotPasswordCheckoutHandler
 */
class ForgotPasswordHandler
{
    private CheckoutHandlerInterface $innerHandler;

    public function __construct(
        private CustomerUserPasswordRequestHandler $passwordRequestHandler,
        private FrontendCustomerUserFormProvider $customerUserFormProvider,
        private RequestStack $requestStack
    ) {
    }

    public function setCheckoutHandler(CheckoutHandlerInterface $handler): void
    {
        $this->innerHandler = $handler;
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function handle(Request $request)
    {
        $workflowItemStub = new WorkflowItem();
        $this->innerHandler->handle($workflowItemStub, $request);

        return (bool)$request->query->get('isCheckEmail');
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    public function isForgotPasswordRequest(Request $request)
    {
        return $this->innerHandler->isSupported($request);
    }
}
