<?php

namespace Oro\Bundle\CustomerBundle\EventListener;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

/**
 * Listener for customer user login events that enables full redirect behavior.
 *
 * This listener intercepts interactive login events and marks the request for full redirect
 * when the authenticated user is a customer user, ensuring proper post-login navigation.
 */
class LoginListener
{
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        if ($event->getAuthenticationToken()->getUser() instanceof CustomerUser) {
            $request = $event->getRequest();

            $request->attributes->set('_fullRedirect', true);
        }
    }
}
