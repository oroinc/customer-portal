<?php

namespace Oro\Bundle\FrontendBundle\EventListener;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\FrontendBundle\GuestAccess\GuestAccessDecisionMakerInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;

class GuestAccessRequestListener
{
    /**
     * @var ConfigManager
     */
    private $configManager;

    /**
     * @var SecurityFacade
     */
    private $securityFacade;

    /**
     * @var GuestAccessDecisionMakerInterface
     */
    private $guestAccessDecisionMaker;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @param SecurityFacade                    $securityFacade
     * @param ConfigManager                     $configManager
     * @param GuestAccessDecisionMakerInterface $guestAccessDeniedDecisionMaker
     * @param RouterInterface                   $router
     */
    public function __construct(
        SecurityFacade $securityFacade,
        ConfigManager $configManager,
        GuestAccessDecisionMakerInterface $guestAccessDeniedDecisionMaker,
        RouterInterface $router
    ) {
        $this->configManager = $configManager;
        $this->securityFacade = $securityFacade;
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

        if ($this->securityFacade->hasLoggedUser()) {
            return;
        }

        if ($this->configManager->get('oro_frontend.guest_access_enabled')) {
            return;
        }

        $decision = $this->guestAccessDecisionMaker->decide($event->getRequest()->getPathInfo());
        if ($decision === GuestAccessDecisionMakerInterface::URL_DISALLOW) {
            throw $this->createNotFoundException();
        }

        if ($decision === GuestAccessDecisionMakerInterface::URL_REDIRECT) {
            $redirectResponse = $this->createRedirectResponse($this->getCustomerUserLoginUrl());
            $event->setResponse($redirectResponse);
        }
    }

    /**
     * @param string $message
     *
     * @return NotFoundHttpException
     */
    private function createNotFoundException($message = 'Not Found')
    {
        return new NotFoundHttpException($message);
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
