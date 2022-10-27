<?php

namespace Oro\Bundle\CustomerBundle\EventListener;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

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
