<?php

namespace Oro\Bundle\CustomerBundle\Security\Listener;

use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Synchronizes and stores customer visitor's last visit date with one stored in the session.
 */
class CustomerVisitorUpdateLastVisitListener
{
    private const SESSION_LAST_VISIT_TIME_FIELD = '_customer_visitor_%s_last_visit_time';

    private TokenStorageInterface $tokenStorage;
    private int $updateLatency;

    public function __construct(TokenStorageInterface $tokenStorage, int $updateLatency)
    {
        $this->tokenStorage = $tokenStorage;
        $this->updateLatency = $updateLatency;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        if ($this->supports($event)) {
            $this->updateLastVisitTime($event);
        }
    }

    private function supports(RequestEvent $event): bool
    {
        return 'cli' !== php_sapi_name()
            && $event->isMainRequest()
            && $event->getRequest()?->getSession()
            && $this->tokenStorage->getToken() instanceof AnonymousCustomerUserToken;
    }

    private function updateLastVisitTime(RequestEvent $event)
    {
        $session = $event->getRequest()->getSession();
        /** @var CustomerVisitor $user */
        $user = $this->tokenStorage->getToken()->getVisitor();
        $sessionField = sprintf(self::SESSION_LAST_VISIT_TIME_FIELD, $user->getSessionId());

        if ($session->has($sessionField)) {
            $previousTime = $session->get($sessionField);
            $currentTime = time();

            if ($this->updateLatency < $currentTime - $previousTime) {
                $session->set($sessionField, $currentTime);
            }
        } else {
            $session->set($sessionField, $user->getLastVisit()->getTimestamp());
        }
    }
}
