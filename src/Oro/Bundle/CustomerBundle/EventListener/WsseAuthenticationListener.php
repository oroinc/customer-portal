<?php

namespace Oro\Bundle\CustomerBundle\EventListener;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\UserBundle\Security\UserLoginAttemptLogger;
use Oro\Bundle\WsseAuthenticationBundle\Security\WsseToken;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;

/**
 * Logs the WSSE login attempts.
 */
class WsseAuthenticationListener
{
    private UserLoginAttemptLogger $logger;

    public function __construct(UserLoginAttemptLogger $logger)
    {
        $this->logger = $logger;
    }

    public function onAuthenticationSuccess(AuthenticationEvent $event): void
    {
        $token = $event->getAuthenticationToken();
        if (!$token instanceof WsseToken) {
            return;
        }

        if ($token->getUser() instanceof CustomerUser) {
            $this->logger->logSuccessLoginAttempt($token->getUser(), 'wsse');
        }
    }
}
