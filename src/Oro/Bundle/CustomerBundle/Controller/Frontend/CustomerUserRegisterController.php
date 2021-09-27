<?php

namespace Oro\Bundle\CustomerBundle\Controller\Frontend;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\CustomerUserEvents;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\CustomerBundle\Event\FilterCustomerUserResponseEvent;
use Oro\Bundle\CustomerBundle\Handler\CustomerRegistrationHandler;
use Oro\Bundle\LayoutBundle\Annotation\Layout;
use Oro\Bundle\UIBundle\Route\Router;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
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
        return (bool) $this->get(ConfigManager::class)->get('oro_customer.registration_allowed');
    }

    /**
     * @param Request $request
     * @return array|RedirectResponse
     */
    protected function handleForm(Request $request)
    {
        $registrationHandler = $this->get(CustomerRegistrationHandler::class);
        $response = $registrationHandler->handleRegistration($request);

        if ($response instanceof Response) {
            /** @var CustomerUser $customerUser */
            $customerUser = $registrationHandler->getForm()->getData();
            $event = new FilterCustomerUserResponseEvent($customerUser, $request, $response);
            $this->get(EventDispatcherInterface::class)->dispatch($event, CustomerUserEvents::REGISTRATION_COMPLETED);

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
        $userManager = $this->get(CustomerUserManager::class);
        $session = $request->getSession();
        $token = $request->get('token');
        if (empty($token)) {
            $session->getFlashBag()->add(
                'error',
                'oro.user.security.confirmation_link_expired_or_used.message'
            );
            throw $this->createNotFoundException('CustomerUser not found or incorrect confirmation token');
        }

        /** @var CustomerUser $customerUser */
        $customerUser = $userManager->findUserByConfirmationToken($token);
        if ($customerUser === null) {
            $session->getFlashBag()->add(
                'error',
                'oro.user.security.confirmation_link_expired_or_used.message'
            );
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

        $session->getFlashBag()->add($messageType, $message);

        if ($request->get(Router::ACTION_PARAMETER)) {
            $response = $this->get(Router::class)->redirect($customerUser);
        } else {
            $response = $this->redirectToRoute('oro_customer_customer_user_security_login');
        }

        $event = new FilterCustomerUserResponseEvent($customerUser, $request, $response);
        $this->get(EventDispatcherInterface::class)->dispatch($event, CustomerUserEvents::REGISTRATION_CONFIRMED);

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                EventDispatcherInterface::class,
                ConfigManager::class,
                CustomerRegistrationHandler::class,
                CustomerUserManager::class,
                Router::class,
            ]
        );
    }
}
