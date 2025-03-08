<?php

namespace Oro\Bundle\CustomerBundle\Security\Listener;

use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Synchronizes and stores customer visitor's last visit date with one stored in the session.
 */
class CustomerVisitorUpdateLastVisitListener
{
    private const string SESSION_LAST_VISIT_TIME_FIELD = '_customer_visitor_%s_last_visit_time';

    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private int $updateLatency
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if ('cli' === php_sapi_name()) {
            return;
        }
        if (!$event->isMainRequest()) {
            return;
        }

        $session = $event->getRequest()->getSession();
        if (!$session->isStarted()) {
            return;
        }

        $token = $this->tokenStorage->getToken();
        if (!$token instanceof AnonymousCustomerUserToken) {
            return;
        }

        $this->updateLastVisitTime($token, $session);
    }

    private function updateLastVisitTime(AnonymousCustomerUserToken $token, SessionInterface $session): void
    {
        /** @var CustomerVisitor $visitor */
        $visitor = $token->getVisitor();
        $sessionField = \sprintf(self::SESSION_LAST_VISIT_TIME_FIELD, $visitor->getSessionId());
        if ($session->has($sessionField)) {
            $previousTime = $session->get($sessionField);
            $currentTime = time();
            if ($this->updateLatency < $currentTime - $previousTime) {
                $session->set($sessionField, $currentTime);
            }
        } else {
            $session->set($sessionField, $visitor->getLastVisit()->getTimestamp());
        }
    }
}
