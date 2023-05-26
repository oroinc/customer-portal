<?php

namespace Oro\Bundle\CustomerBundle\Handler;

use Oro\Bundle\CustomerBundle\Form\Handler\CustomerUserPasswordRequestHandler;
use Oro\Bundle\CustomerBundle\Layout\DataProvider\FrontendCustomerUserFormProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Handling forgot password request during checkout
 */
class ForgotPasswordHandler
{
    public function __construct(
        private CustomerUserPasswordRequestHandler $passwordRequestHandler,
        private FrontendCustomerUserFormProvider $customerUserFormProvider,
        private RequestStack $requestStack,
    ) {
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function handle(Request $request)
    {
        if (!$this->isForgotPasswordRequest($request)) {
            return false;
        }
        $form = $this->customerUserFormProvider->getForgotPasswordForm();
        $email = $this->passwordRequestHandler->process($form, $request);
        if (!$email) {
            return false;
        }

        $request->query->remove('isForgotPassword');
        $request->query->add(['isCheckEmail' => true]);
        $this->requestStack->getSession()->set(
            'oro_customer_user_reset_email',
            $email
        );

        return true;
    }

    /**
     * @param $request
     *
     * @return bool
     */
    public function isForgotPasswordRequest(Request $request)
    {
        return $request->isMethod(Request::METHOD_POST) && $request->get('isForgotPassword') !== null;
    }
}
