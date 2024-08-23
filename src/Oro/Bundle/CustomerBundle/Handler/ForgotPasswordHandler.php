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
 */
class ForgotPasswordHandler implements CheckoutHandlerInterface
{
    public function __construct(
        private CustomerUserPasswordRequestHandler $passwordRequestHandler,
        private FrontendCustomerUserFormProvider $customerUserFormProvider,
        private RequestStack $requestStack,
    ) {
    }

    public function handle(WorkflowItem $workflowItem, Request $request): void
    {
        if (!$this->isSupported($request)) {
            return;
        }

        $form = $this->customerUserFormProvider->getForgotPasswordForm();
        $email = $this->passwordRequestHandler->process($form, $request);
        if (!$email) {
            return;
        }

        $request->query->remove('isForgotPassword');
        $request->query->add(['isCheckEmail' => true]);
        $this->requestStack->getSession()->set(
            'oro_customer_user_reset_email',
            $email
        );
    }

    public function isSupported(Request $request): bool
    {
        return $request->isMethod(Request::METHOD_POST) && $request->get('isForgotPassword') !== null;
    }
}
