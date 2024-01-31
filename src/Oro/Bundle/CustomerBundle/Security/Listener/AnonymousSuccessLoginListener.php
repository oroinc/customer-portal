<?php

namespace Oro\Bundle\CustomerBundle\Security\Listener;

use Oro\Bundle\SecurityBundle\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

/**
 * Listener to stop login success event for anonynous customer user.
 */
class AnonymousSuccessLoginListener
{
    public function onSuccess(LoginSuccessEvent $event): void
    {
        if ($event->getAuthenticatedToken() instanceof AnonymousToken) {
            // login success event is not suported for customer visitor "anonymous" token
            $event->stopPropagation();
        }
    }
}
