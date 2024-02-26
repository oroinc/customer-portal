<?php

namespace Oro\Bundle\CustomerBundle\Controller\Frontend;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\CustomerBundle\Form\Handler\CustomerUserPasswordRequestHandler;
use Oro\Bundle\CustomerBundle\Form\Handler\CustomerUserPasswordResetHandler;
use Oro\Bundle\CustomerBundle\Layout\DataProvider\FrontendCustomerUserFormProvider;
use Oro\Bundle\LayoutBundle\Attribute\Layout;
use Oro\Bundle\UIBundle\Route\Router;
use Oro\Bundle\UserBundle\Entity\AbstractUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Handles request and reset password logic
 */
class ResetController extends AbstractController
{
    const SESSION_EMAIL = 'oro_customer_user_reset_email';

    #[Route(
        path: '/reset-request',
        name: 'oro_customer_frontend_customer_user_reset_request',
        methods: ['GET', 'POST']
    )]
    #[Layout]
    public function requestAction()
    {
        if ($this->getUser() instanceof AbstractUser) {
            return $this->redirect($this->generateUrl('oro_customer_frontend_customer_user_profile'));
        }

        /** @var CustomerUserPasswordRequestHandler $handler */
        $handler = $this->container->get(CustomerUserPasswordRequestHandler::class);
        $form = $this->container->get(FrontendCustomerUserFormProvider::class)
            ->getForgotPasswordForm();

        $request = $this->container->get('request_stack')->getCurrentRequest();
        $email = $handler->process($form, $request);
        if ($email) {
            $request->getSession()->set(static::SESSION_EMAIL, $email);
            return $this->redirect($this->generateUrl('oro_customer_frontend_customer_user_reset_check_email'));
        }

        return [];
    }

    /**
     * Tell the user to check his email
     */
    #[Route(path: '/check-email', name: 'oro_customer_frontend_customer_user_reset_check_email', methods: ['GET'])]
    #[Layout]
    public function checkEmailAction(Request $request)
    {
        $session = $request->getSession();
        $email = $session->get(static::SESSION_EMAIL);
        $session->remove(static::SESSION_EMAIL);

        if (empty($email)) {
            // the user does not come from the sendEmail action
            return $this->redirect($this->generateUrl('oro_customer_frontend_customer_user_reset_request'));
        }

        return [
            'data' => [
                'email' => $email
            ]
        ];
    }

    /**
     * Reset user password
     *
     * @param Request $request
     * @return array|RedirectResponse
     */
    #[Route(path: '/reset', name: 'oro_customer_frontend_customer_user_password_reset', methods: ['GET', 'POST'])]
    #[Layout]
    public function resetAction(Request $request)
    {
        $token = $request->get('token');
        $user = null;
        if ($token) {
            /** @var CustomerUser $user */
            $user = $this->getUserManager()->findUserByConfirmationToken($token);
        }
        if (null === $user) {
            throw $this->createNotFoundException(
                $this->container->get(TranslatorInterface::class)->trans(
                    'oro.customer.controller.customeruser.token_not_found.message',
                    ['%token%' => $token]
                )
            );
        }

        $session = $request->getSession();
        $ttl = $this->getParameter('oro_customer_user.reset.ttl');
        if (!$user->isPasswordRequestNonExpired($ttl)) {
            $session->getFlashBag()->add(
                'warn',
                'oro.customer.customeruser.profile.password.reset.ttl_expired.message'
            );

            return $this->redirect($this->generateUrl('oro_customer_frontend_customer_user_reset_request'));
        }

        /** @var CustomerUserPasswordResetHandler $handler */
        $handler = $this->container->get(CustomerUserPasswordResetHandler::class);
        $form = $this->container->get(FrontendCustomerUserFormProvider::class)
            ->getResetPasswordForm($user);

        $actionParameter = $request->get(Router::ACTION_PARAMETER);
        if ($handler->process($form, $request)) {
            // force user logout
            $session->invalidate();
            $this->container->get('security.token_storage')->setToken(null);

            $session->getFlashBag()->add(
                'success',
                'oro.customer.customeruser.profile.password_reset.message'
            );

            if ($actionParameter) {
                $response = $this->container->get(Router::class)->redirect($user);
            } else {
                $response = $this->redirect($this->generateUrl('oro_customer_customer_user_security_login'));
            }

            return $response;
        }

        return [
            'data' => [
                'user' => $user,
                Router::ACTION_PARAMETER => $actionParameter ?
                    $actionParameter : json_encode(['route' => 'oro_customer_customer_user_security_login'])
            ]
        ];
    }

    protected function getUserManager(): CustomerUserManager
    {
        return $this->container->get(CustomerUserManager::class);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices(): array
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                TranslatorInterface::class,
                CustomerUserPasswordRequestHandler::class,
                FrontendCustomerUserFormProvider::class,
                CustomerUserPasswordResetHandler::class,
                Router::class,
                CustomerUserManager::class,
            ]
        );
    }
}
