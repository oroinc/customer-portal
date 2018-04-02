<?php

namespace Oro\Bundle\CustomerBundle\Controller;

use Oro\Bundle\LayoutBundle\Annotation\Layout;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class SecurityController extends Controller
{
    /**
     * @Route("/login", name="oro_customer_customer_user_security_login")
     * @Layout()
     */
    public function loginAction(Request $request)
    {
        if ($this->getUser()) {
            return $this->redirect($this->generateUrl('oro_customer_frontend_customer_user_profile'));
        }

        // 302 redirect does not processed by Backbone.sync handler, but 401 error does.
        if ($request->isXmlHttpRequest()) {
            //the redirectUrl needed to redirect user to the login page.
            $redirectUrl = $this->generateUrl('oro_customer_customer_user_security_login');
            return new JsonResponse(['redirectUrl' => $redirectUrl], 401);
        }

        $registrationAllowed = (bool) $this->get('oro_config.manager')->get('oro_customer.registration_allowed');

        return [
            'data' => [
                'registrationAllowed' => $registrationAllowed
            ]
        ];
    }

    /**
     * @Route("/login-check", name="oro_customer_customer_user_security_check")
     */
    public function checkAction()
    {
        throw new \RuntimeException(
            'You must configure the check path to be handled by the firewall ' .
            'using form_login in your security firewall configuration.'
        );
    }

    /**
     * @Route("/logout", name="oro_customer_customer_user_security_logout")
     */
    public function logoutAction()
    {
        throw new \RuntimeException('You must activate the logout in your security firewall configuration.');
    }
}
