<?php

namespace Oro\Bundle\CustomerBundle\EventListener;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\CustomerUserEvents;
use Oro\Bundle\CustomerBundle\Event\FilterCustomerUserResponseEvent;
use Oro\Bundle\CustomerBundle\Security\LoginManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Authenticates CustomerUser depending on response param or config settings
 */
class AuthenticationListener implements EventSubscriberInterface
{
    const AUTO_LOGIN_PARAM = "_oro_customer_auto_login";

    /** @var LoginManager */
    private $loginManager;

    /** @var ConfigManager */
    private $configManager;

    /** @var string */
    private $firewallName;

    /**
     * @param LoginManager $loginManager
     * @param ConfigManager $configManager
     * @param string $firewallName
     */
    public function __construct(
        LoginManager $loginManager,
        ConfigManager $configManager,
        $firewallName
    ) {
        $this->loginManager = $loginManager;
        $this->configManager = $configManager;
        $this->firewallName = $firewallName;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            CustomerUserEvents::REGISTRATION_COMPLETED => 'authenticate',
            CustomerUserEvents::REGISTRATION_CONFIRMED => 'authenticate'
        ];
    }

    public function authenticate(FilterCustomerUserResponseEvent $event)
    {
        $request = $event->getRequest();
        $customerUser = $event->getCustomerUser();

        if ($this->configManager->get('oro_customer.auto_login_after_registration') ||
            $request->get(self::AUTO_LOGIN_PARAM)
        ) {
            $this->loginManager->logInUser($this->firewallName, $customerUser, $event->getResponse());
        }
    }
}
