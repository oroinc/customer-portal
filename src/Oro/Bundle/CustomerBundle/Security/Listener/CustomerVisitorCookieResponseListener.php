<?php

namespace Oro\Bundle\CustomerBundle\Security\Listener;

use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;
use Oro\Bundle\CustomerBundle\Security\AnonymousCustomerUserAuthenticator;
use Oro\Bundle\CustomerBundle\Security\Firewall\CustomerVisitorCookieFactory;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Sets customer visitor cookie from the request attribute to response.
 */
class CustomerVisitorCookieResponseListener
{
    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private CustomerVisitorCookieFactory $cookieFactory,
    ) {
    }

    /**
     * Set cookie from request attribute to response
     */
    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $response = $event->getResponse();
        if ($request->attributes->has(AnonymousCustomerUserAuthenticator::COOKIE_ATTR_NAME)) {
            $customerVisitorCookie = $request->attributes->get(AnonymousCustomerUserAuthenticator::COOKIE_ATTR_NAME);
            $user = $this->tokenStorage->getToken()?->getUser();
            [$visitorId, $sessionId] = json_decode(base64_decode($customerVisitorCookie->getValue()));

            // update the customer visitor cookie from anonymous to stateful if necessary
            $cookie = $user instanceof CustomerVisitor
            && !$user->isAnonymous()
            && (is_string($sessionId) && CustomerVisitor::isAnonymousSession($sessionId))
                ? $this->cookieFactory->getCookie($user->getSessionId(), $user->getId())
                : $customerVisitorCookie;

            $response->headers->setCookie($cookie);
        }
    }
}
