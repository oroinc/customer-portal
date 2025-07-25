<?php

namespace Oro\Bundle\CustomerBundle\Controller;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\LayoutBundle\Attribute\Layout;
use Oro\Bundle\UserBundle\Entity\AbstractUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Controller responsible for customer user authorization
 */
class SecurityController extends AbstractController
{
    /**
     *
     * @param Request $request
     * @return array|RedirectResponse
     */
    #[Route(path: '/login', name: 'oro_customer_customer_user_security_login')]
    #[Layout]
    public function loginAction(Request $request)
    {
        if ($this->getUser() instanceof AbstractUser) {
            return $this->redirect($this->generateUrl('oro_customer_frontend_customer_user_profile'));
        }

        // 302 redirect does not processed by Backbone.sync handler, but 401 error does.
        if ($request->isXmlHttpRequest()) {
            //the redirectUrl needed to redirect user to the login page.
            $redirectUrl = $this->generateUrl('oro_customer_customer_user_security_login');
            return new JsonResponse(['redirectUrl' => $redirectUrl], 401);
        }

        $registrationAllowed = (bool) $this->container->get(ConfigManager::class)
            ->get('oro_customer.registration_allowed');

        return [
            'data' => [
                'registrationAllowed' => $registrationAllowed
            ]
        ];
    }

    /**
     *
     * @param Request $request
     * @return RedirectResponse
     */
    #[Route(path: '/login-check', name: 'oro_customer_customer_user_security_check')]
    public function checkAction(Request $request)
    {
        if (!$request->isMethod('POST')) {
            return $this->redirectToRoute(
                $this->getUser() instanceof AbstractUser
                    ? 'oro_customer_frontend_customer_user_profile'
                    : 'oro_customer_customer_user_security_login'
            );
        }

        throw new \RuntimeException(
            'You must configure the check path to be handled by the firewall ' .
            'using form_login in your security firewall configuration.'
        );
    }

    #[Route(path: '/logout', name: 'oro_customer_customer_user_security_logout')]
    public function logoutAction()
    {
        throw new \RuntimeException('You must activate the logout in your security firewall configuration.');
    }

    #[\Override]
    public static function getSubscribedServices(): array
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                ConfigManager::class,
            ]
        );
    }
}
