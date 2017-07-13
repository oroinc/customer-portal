<?php

namespace Oro\Bundle\CustomerBundle\EventListener;

use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Security\Core\Exception\LockedException;
use Symfony\Component\Security\Http\Logout\LogoutUrlGenerator;

class DisabledUserSessionListener
{
    /**
     * @var FrontendHelper
     */
    private $frontendHelper;

    /**
     * @var LogoutUrlGenerator
     */
    private $logoutUrlGenerator;

    /**
     * @param LogoutUrlGenerator $logoutUrlGenerator
     * @param FrontendHelper     $frontendHelper
     */
    public function __construct(LogoutUrlGenerator $logoutUrlGenerator, FrontendHelper $frontendHelper)
    {
        $this->logoutUrlGenerator = $logoutUrlGenerator;
        $this->frontendHelper = $frontendHelper;
    }

    /**
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        if ($exception instanceof LockedException && $this->frontendHelper->isFrontendRequest($event->getRequest())) {
            $response = new RedirectResponse($this->logoutUrlGenerator->getLogoutUrl());
            $event->setResponse($response);
        }
    }
}
