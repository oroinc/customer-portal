<?php

namespace Oro\Bundle\CustomerBundle\Security\Listener;

use Oro\Bundle\CustomerBundle\Security\Firewall\AnonymousCustomerUserAuthenticationListener;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class CustomerVisitorCookieResponseListener
{
    /**
     * Set cookie from request attribute to response
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();
        $response = $event->getResponse();
        if ($request->attributes->has(AnonymousCustomerUserAuthenticationListener::COOKIE_ATTR_NAME)) {
            $response->headers->setCookie(
                $request->attributes->get(AnonymousCustomerUserAuthenticationListener::COOKIE_ATTR_NAME)
            );
        }
    }
}
