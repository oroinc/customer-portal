<?php

namespace Oro\Bundle\FrontendBundle\EventListener;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\FrontendBundle\GuestAccess\GuestAccessDecisionMakerInterface;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Routing\RouterInterface;

/**
 * This listener should be triggered only after firewall and authentication are passed, routing and slugs are resolved
 * in Oro\Bundle\RedirectBundle\Security\Firewall (security.firewall service). Otherwise it'll become bug prone due to:
 *  - Prematurely initialized user configuration scope (user is not authenticated yet, but ConfigManager is called)
 *  - Prematurely allowance for the URL that might become closed after slugs are resolved
 *
 * Hence, it should be triggered after the priority 8. In order to give others ability to change the behavior, it was
 * decided to put this listener on priority 5.
 */
class GuestAccessRequestListener
{
    /**
     * @var TokenAccessorInterface
     */
    private $tokenAccessor;

    /**
     * @var ConfigManager
     */
    private $configManager;

    /**
     * @var GuestAccessDecisionMakerInterface
     */
    private $guestAccessDecisionMaker;
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @param TokenAccessorInterface            $tokenAccessor
     * @param ConfigManager                     $configManager
     * @param GuestAccessDecisionMakerInterface $guestAccessDeniedDecisionMaker
     * @param RouterInterface                   $router
     */
    public function __construct(
        TokenAccessorInterface $tokenAccessor,
        ConfigManager $configManager,
        GuestAccessDecisionMakerInterface $guestAccessDeniedDecisionMaker,
        RouterInterface $router
    ) {
        $this->tokenAccessor = $tokenAccessor;
        $this->configManager = $configManager;
        $this->guestAccessDecisionMaker = $guestAccessDeniedDecisionMaker;
        $this->router = $router;
    }

    /**
     * @param GetResponseEvent $event
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        if ($this->tokenAccessor->hasUser()) {
            return;
        }

        if ($this->configManager->get('oro_frontend.guest_access_enabled')) {
            return;
        }

        $decision = $this->guestAccessDecisionMaker->decide($event->getRequest()->getPathInfo());
        if ($decision === GuestAccessDecisionMakerInterface::URL_DISALLOW) {
            $redirectResponse = $this->createRedirectResponse($this->getCustomerUserLoginUrl());
            $event->setResponse($redirectResponse);
        }
    }

    /**
     * @param string $url
     * @param int    $status
     *
     * @return RedirectResponse
     */
    private function createRedirectResponse($url, $status = 302)
    {
        return new RedirectResponse($url, $status);
    }

    /**
     * @return string
     */
    private function getCustomerUserLoginUrl()
    {
        return $this->router->generate('oro_customer_customer_user_security_login');
    }
}
