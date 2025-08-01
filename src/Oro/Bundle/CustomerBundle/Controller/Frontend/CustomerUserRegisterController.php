<?php

namespace Oro\Bundle\CustomerBundle\Controller\Frontend;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\CustomerUserEvents;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\CustomerBundle\Event\FilterCustomerUserResponseEvent;
use Oro\Bundle\CustomerBundle\Handler\CustomerRegistrationHandler;
use Oro\Bundle\LayoutBundle\Attribute\Layout;
use Oro\Bundle\UIBundle\Route\Router;
use Oro\Bundle\UserBundle\Entity\AbstractUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Handles CustomerUser registration logic
 */
class CustomerUserRegisterController extends AbstractController
{
    /**
     * Create customer user form
     *
     * @param Request $request
     * @return array|RedirectResponse
     */
    #[Route(path: '/registration', name: 'oro_customer_frontend_customer_user_register')]
    #[Layout]
    public function registerAction(Request $request)
    {
        if ($this->getUser() instanceof AbstractUser) {
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
        return (bool) $this->container->get(ConfigManager::class)->get('oro_customer.registration_allowed');
    }

    /**
     * @param Request $request
     * @return array|RedirectResponse
     */
    protected function handleForm(Request $request)
    {
        $registrationHandler = $this->container->get(CustomerRegistrationHandler::class);
        $response = $registrationHandler->handleRegistration($request);

        if ($response instanceof Response) {
            /** @var CustomerUser $customerUser */
            $customerUser = $registrationHandler->getForm()->getData();
            if ($customerUser->getId()) {
                $event = new FilterCustomerUserResponseEvent($customerUser, $request, $response);
                $this->container->get(EventDispatcherInterface::class)->dispatch(
                    $event,
                    CustomerUserEvents::REGISTRATION_COMPLETED
                );
            }

            return $response;
        }

        return [];
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    #[Route(path: '/confirm-email', name: 'oro_customer_frontend_customer_user_confirmation')]
    public function confirmEmailAction(Request $request)
    {
        $userManager = $this->container->get(CustomerUserManager::class);
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
            $response = $this->container->get(Router::class)->redirect($customerUser);
        } else {
            $response = $this->redirectToRoute('oro_customer_customer_user_security_login');
        }

        $event = new FilterCustomerUserResponseEvent($customerUser, $request, $response);
        $this->container->get(EventDispatcherInterface::class)->dispatch(
            $event,
            CustomerUserEvents::REGISTRATION_CONFIRMED
        );

        return $response;
    }

    #[\Override]
    public static function getSubscribedServices(): array
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
