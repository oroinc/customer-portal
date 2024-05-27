<?php

namespace Oro\Bundle\CustomerBundle\EventListener;

use Oro\Bundle\CustomerBundle\Event\CustomerUserEmailSendEvent;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Responsible for the context of emails for anonymous users.
 * Emails sent from anonymous users should be restricted only to the scope and sender specified for the current website.
 */
class AnonymousUserEmailSendListener
{
    public function __construct(private TokenStorageInterface $tokenStorage, private WebsiteManager $websiteManager)
    {
    }

    public function onCustomerUserEmailSend(CustomerUserEmailSendEvent $event): void
    {
        if (!$this->tokenStorage->getToken() instanceof AnonymousCustomerUserToken) {
            return;
        }

        $website = $this->websiteManager->getCurrentWebsite();
        $event->setScope($website);
    }
}
