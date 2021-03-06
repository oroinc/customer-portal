<?php

namespace Oro\Bundle\CustomerBundle\Security\Listener;

use Oro\Bundle\CustomerBundle\Security\Firewall\AnonymousCustomerUserAuthenticationListener;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

/**
 * Sets customer visitor cookie from the request attribute to response.
 */
class CustomerVisitorCookieResponseListener
{
    /**
     * Set cookie from request attribute to response
     *
     * @param ResponseEvent $event
     */
    public function onKernelResponse(ResponseEvent $event): void
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
