<?php

namespace Oro\Bundle\CustomerBundle\Controller\Frontend;

use Oro\Bundle\CustomerBundle\CustomerUserEvents;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Event\FilterCustomerUserResponseEvent;
use Oro\Bundle\LayoutBundle\Annotation\Layout;
use Oro\Bundle\UIBundle\Route\Router;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Handles CustomerUser registration logic
 */
class CustomerUserRegisterController extends AbstractController
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
        $registrationHandler = $this->get('oro_customer.handler.customer_registration_handler');
        $response = $registrationHandler->handleRegistration($request);

        if ($response instanceof Response) {
            /** @var CustomerUser $customerUser */
            $customerUser = $registrationHandler->getForm()->getData();
            $event = new FilterCustomerUserResponseEvent($customerUser, $request, $response);
            $this->get('event_dispatcher')->dispatch($event, CustomerUserEvents::REGISTRATION_COMPLETED);

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
        $token = $request->get('token');
        if (empty($token)) {
            throw $this->createNotFoundException('CustomerUser not found or incorrect confirmation token');
        }

        /** @var CustomerUser $customerUser */
        $customerUser = $userManager->findUserByConfirmationToken($token);
        if ($customerUser === null) {
            throw $this->createNotFoundException('CustomerUser not found or incorrect confirmation token');
        }

        $messageType = 'warn';
        $message = 'oro.customer.controller.customeruser.already_confirmed.message';
        if (!$customerUser->isConfirmed()) {
            $userManager->confirmRegistration($customerUser);
            $userManager->updateUser($customerUser);
            $messageType = 'success';
            $message = 'oro.customer.controller.customeruser.confirmed.message';
        }

        $this->get('session')->getFlashBag()->add($messageType, $message);

        if ($request->get(Router::ACTION_PARAMETER)) {
            $response = $this->get('oro_ui.router')->redirect($customerUser);
        } else {
            $response = $this->redirectToRoute('oro_customer_customer_user_security_login');
        }

        $event = new FilterCustomerUserResponseEvent($customerUser, $request, $response);
        $this->get('event_dispatcher')->dispatch($event, CustomerUserEvents::REGISTRATION_CONFIRMED);

        return $response;
    }
}
