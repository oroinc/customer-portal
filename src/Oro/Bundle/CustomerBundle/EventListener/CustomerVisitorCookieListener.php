<?php

namespace Oro\Bundle\CustomerBundle\EventListener;

use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;
use Oro\Bundle\CustomerBundle\Security\Firewall\AnonymousCustomerUserAuthenticationListener;
use Oro\Bundle\CustomerBundle\Security\Firewall\CustomerVisitorCookieFactory;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Listens to CustomerVisitor persistence events and creates cookies after visitor persistence.
 */
class CustomerVisitorCookieListener
{
    public function __construct(
        private CustomerVisitorCookieFactory $cookieFactory,
        private RequestStack $requestStack
    ) {
    }

    public function createCookieIfNeeded(CustomerVisitor $customerVisitor): void
    {
        if (!$customerVisitor->getSessionId()) {
            return;
        }

        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return;
        }

        $existingCookie = $request->cookies->get(AnonymousCustomerUserAuthenticationListener::COOKIE_NAME);
        if ($existingCookie) {
            return;
        }

        $request->attributes->set(
            AnonymousCustomerUserAuthenticationListener::COOKIE_ATTR_NAME,
            $this->cookieFactory->getCookie($customerVisitor->getId(), $customerVisitor->getSessionId())
        );
    }

    public function postPersist(CustomerVisitor $visitor): void
    {
        $this->createCookieIfNeeded($visitor);
    }
}
