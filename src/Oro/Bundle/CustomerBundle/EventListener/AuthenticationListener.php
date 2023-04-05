<?php

namespace Oro\Bundle\CustomerBundle\EventListener;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\CustomerUserEvents;
use Oro\Bundle\CustomerBundle\Event\FilterCustomerUserResponseEvent;
use Oro\Bundle\CustomerBundle\Security\LoginManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Authenticates CustomerUser depending on response param or config settings on success registration or confirmation.
 */
class AuthenticationListener implements EventSubscriberInterface
{
    const AUTO_LOGIN_PARAM = "_oro_customer_auto_login";

    private LoginManager $loginManager;
    private ConfigManager $configManager;
    private string $firewallName;

    public function __construct(LoginManager $loginManager, ConfigManager $configManager, $firewallName)
    {
        $this->loginManager = $loginManager;
        $this->configManager = $configManager;
        $this->firewallName = $firewallName;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            CustomerUserEvents::REGISTRATION_COMPLETED => 'authenticate',
            CustomerUserEvents::REGISTRATION_CONFIRMED => 'authenticateOnRegistrationConfirmed'
        ];
    }

    public function authenticate(FilterCustomerUserResponseEvent $event): void
    {
        if ($event->getRequest()?->get(self::AUTO_LOGIN_PARAM)
            || (
                $this->configManager->get('oro_customer.auto_login_after_registration')
                && !$this->configManager->get('oro_customer.confirmation_required')
            )
        ) {
            $this->loginManager->logInUser($this->firewallName, $event->getCustomerUser(), $event->getResponse());
        }
    }

    public function authenticateOnRegistrationConfirmed(FilterCustomerUserResponseEvent $event): void
    {
        if ($this->configManager->get('oro_customer.auto_login_after_registration')
            || $event->getRequest()?->get(self::AUTO_LOGIN_PARAM)
        ) {
            $this->loginManager->logInUser($this->firewallName, $event->getCustomerUser(), $event->getResponse());
        }
    }
}
