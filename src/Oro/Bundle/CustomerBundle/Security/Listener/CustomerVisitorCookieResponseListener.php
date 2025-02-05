<?php

namespace Oro\Bundle\CustomerBundle\Security\Listener;

use Oro\Bundle\CustomerBundle\Security\AnonymousCustomerUserAuthenticator;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

/**
 * Sets customer visitor cookie from the request attribute to response.
 */
class CustomerVisitorCookieResponseListener
{
    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        if ($request->attributes->has(AnonymousCustomerUserAuthenticator::COOKIE_ATTR_NAME)) {
            $event->getResponse()->headers->setCookie(
                $request->attributes->get(AnonymousCustomerUserAuthenticator::COOKIE_ATTR_NAME)
            );
        }
    }
}
