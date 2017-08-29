<?php

namespace Oro\Bundle\CustomerBundle\Controller\Frontend;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Oro\Bundle\LayoutBundle\Annotation\Layout;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\UIBundle\Route\Router;

class CustomerUserRegisterController extends Controller
{
    /**
     * Create customer user form
     *
     * @Route("/registration", name="oro_customer_frontend_customer_user_register")
     * @Layout()
     * @param Request $request
     * @return array|RedirectResponse
     */
    public function registerAction(Request $request)
    {
        if ($this->getUser()) {
            return $this->redirect($this->generateUrl('oro_customer_frontend_customer_user_profile'));
        }

        if (!$this->isRegistrationAllowed()) {
            return $this->redirect($this->generateUrl('oro_customer_customer_user_security_login'));
        }

        return $this->handleForm($request);
    }

    /**
     * @return bool
     */
    protected function isRegistrationAllowed()
    {
        return (bool) $this->get('oro_config.manager')->get('oro_customer.registration_allowed');
    }

    /**
     * @param Request $request
     * @return array|RedirectResponse
     */
    protected function handleForm(Request $request)
    {
        $form = $this->get('oro_customer.provider.frontend_customer_user_registration_form')
            ->getRegisterForm();
        $userManager = $this->get('oro_customer_user.manager');

        $registrationMessage = 'oro.customer.controller.customeruser.registered.message';
        if ($userManager->isConfirmationRequired()) {
            $registrationMessage = 'oro.customer.controller.customeruser.registered_with_confirmation.message';
        }

        $handler = $this->get('oro_customer.handler.frontend_customer_user_handler');

        $response = $this->get('oro_form.update_handler')->update(
            $form->getData(),
            $form,
            $this->get('translator')->trans($registrationMessage),
            $request,
            $handler
        );

        if ($response instanceof Response) {
            return $response;
        }

        return [];
    }

    /**
     * @Route("/confirm-email", name="oro_customer_frontend_customer_user_confirmation")
     * @param Request $request
     * @return RedirectResponse
     */
    public function confirmEmailAction(Request $request)
    {
        $userManager = $this->get('oro_customer_user.manager');
        /** @var CustomerUser $customerUser */
        $customerUser = $userManager->findUserByUsernameOrEmail($request->get('username'));
        $token = $request->get('token');
        if ($customerUser === null || empty($token) || $customerUser->getConfirmationToken() !== $token) {
            throw $this->createNotFoundException(
                $this->get('translator')
                    ->trans('oro.customer.controller.customeruser.confirmation_error.message')
            );
        }

        $messageType = 'warn';
        $message = 'oro.customer.controller.customeruser.already_confirmed.message';
        if (!$customerUser->isConfirmed()) {
            $userManager->confirmRegistration($customerUser);
            $userManager->updateUser($customerUser);
            $messageType = 'success';
            $message = 'oro.customer.controller.customeruser.confirmed.message';
        }

        if ($this->get('oro_config.manager')->get('oro_customer.auto_login_after_registration')) {
            $this->get('oro_customer.manager.login_manager')->logInUser('frontend_secure', $customerUser);
        }

        $this->get('session')->getFlashBag()->add($messageType, $message);

        if ($request->get(Router::ACTION_PARAMETER)) {
            return $this->get('oro_ui.router')->redirect($customerUser);
        }

        return $this->redirect($this->generateUrl('oro_customer_customer_user_security_login'));
    }
}
