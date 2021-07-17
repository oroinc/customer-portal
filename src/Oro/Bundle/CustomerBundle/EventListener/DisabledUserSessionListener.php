<?php

namespace Oro\Bundle\CustomerBundle\EventListener;

use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Security\Core\Exception\LockedException;
use Symfony\Component\Security\Http\Logout\LogoutUrlGenerator;

/**
 * Sets the redirect to logout URL if the storefront user is locked.
 */
class DisabledUserSessionListener
{
    /** @var FrontendHelper */
    private $frontendHelper;

    /** @var LogoutUrlGenerator */
    private $logoutUrlGenerator;

    public function __construct(LogoutUrlGenerator $logoutUrlGenerator, FrontendHelper $frontendHelper)
    {
        $this->logoutUrlGenerator = $logoutUrlGenerator;
        $this->frontendHelper = $frontendHelper;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if ($event->getThrowable() instanceof LockedException
            && $this->frontendHelper->isFrontendUrl($event->getRequest()->getPathInfo())
        ) {
            $event->setResponse(new RedirectResponse($this->logoutUrlGenerator->getLogoutUrl()));
        }
    }
}
