<?php

namespace Oro\Bundle\CustomerBundle\EventListener;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\Manager\LoginManager;
use Oro\Bundle\FormBundle\Event\FormHandler\AfterFormProcessEvent;

class AutoLoginListener
{
    /** @var LoginManager */
    private $loginManager;

    /** @var ConfigManager */
    private $configManager;

    /**
     * @param LoginManager $loginManager
     * @param ConfigManager $configManager
     */
    public function __construct(
        LoginManager $loginManager,
        ConfigManager $configManager
    ) {
        $this->loginManager = $loginManager;
        $this->configManager = $configManager;
    }

    /**
     * @param AfterFormProcessEvent $event
     */
    public function afterFlush(AfterFormProcessEvent $event)
    {
        $customerUser = $event->getData();
        if ($customerUser->isConfirmed() && $this->configManager->get('oro_customer.auto_login_after_registration')) {
            $this->loginManager->logInUser('frontend_secure', $customerUser);
        }
    }
}
