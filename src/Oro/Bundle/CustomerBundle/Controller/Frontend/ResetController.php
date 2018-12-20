<?php

namespace Oro\Bundle\CustomerBundle\Controller\Frontend;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\CustomerBundle\Form\Handler\CustomerUserPasswordRequestHandler;
use Oro\Bundle\CustomerBundle\Form\Handler\CustomerUserPasswordResetHandler;
use Oro\Bundle\LayoutBundle\Annotation\Layout;
use Oro\Bundle\UIBundle\Route\Router;
use Oro\Bundle\UserBundle\Util\ObfuscatedEmailTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Handles request and reset password logic
 */
class ResetController extends Controller
{
    use ObfuscatedEmailTrait;

    const SESSION_EMAIL = 'oro_customer_user_reset_email';

    /**
     * @Layout()
     * @Route("/reset-request", name="oro_customer_frontend_customer_user_reset_request")
     * @Method({"GET", "POST"})
     */
    public function requestAction()
    {
        if ($this->getUser()) {
            return $this->redirect($this->generateUrl('oro_customer_frontend_customer_user_profile'));
        }

        /** @var CustomerUserPasswordRequestHandler $handler */
        $handler = $this->get('oro_customer.customer_user.password_request.handler');
        $form = $this->get('oro_customer.provider.frontend_customer_user_form')
            ->getForgotPasswordForm();

        $request = $this->get('request_stack')->getCurrentRequest();
        $user = $handler->process($form, $request);
        if ($user) {
            $this->get('session')->set(static::SESSION_EMAIL, $this->getObfuscatedEmail($user->getEmail()));
            return $this->redirect($this->generateUrl('oro_customer_frontend_customer_user_reset_check_email'));
        }

        return [];
    }

    /**
     * Tell the user to check his email
     *
     * @Layout()
     * @Route("/check-email", name="oro_customer_frontend_customer_user_reset_check_email")
     * @Method({"GET"})
     */
    public function checkEmailAction()
    {
        $session = $this->get('session');
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
     * @Layout
     * @Route("/reset", name="oro_customer_frontend_customer_user_password_reset")
     * @Method({"GET", "POST"})
     * @param Request $request
     * @return array|RedirectResponse
     */
    public function resetAction(Request $request)
    {
        $token = $request->get('token');
        $username = $request->get('username');
        /** @var CustomerUser $user */
        $user = $this->getUserManager()->findUserByUsernameOrEmail($username);
        if (!$token || null === $user || $user->getConfirmationToken() !== $token) {
            throw $this->createNotFoundException(
                $this->get('translator')->trans(
                    'oro.customer.controller.customeruser.token_not_found.message',
                    ['%token%' => $token]
                )
            );
        }

        $session = $this->get('session');
        $ttl = $this->container->getParameter('oro_user.reset.ttl');
        if (!$user->isPasswordRequestNonExpired($ttl)) {
            $session->getFlashBag()->add(
                'warn',
                'oro.customer.customeruser.profile.password.reset.ttl_expired.message'
            );

            return $this->redirect($this->generateUrl('oro_customer_frontend_customer_user_reset_request'));
        }

        /** @var CustomerUserPasswordResetHandler $handler */
        $handler = $this->get('oro_customer.customer_user.password_reset.handler');
        $form = $this->get('oro_customer.provider.frontend_customer_user_form')
            ->getResetPasswordForm($user);

        $actionParameter = $request->get(Router::ACTION_PARAMETER);
        if ($handler->process($form, $request)) {
            // force user logout
            $session->invalidate();
            $this->get('security.token_storage')->setToken(null);

            $session->getFlashBag()->add(
                'success',
                'oro.customer.customeruser.profile.password_reset.message'
            );

            if ($actionParameter) {
                $response = $this->get('oro_ui.router')->redirect($user);
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

    /**
     * @return CustomerUserManager
     */
    protected function getUserManager()
    {
        return $this->get('oro_customer_user.manager');
    }
}
