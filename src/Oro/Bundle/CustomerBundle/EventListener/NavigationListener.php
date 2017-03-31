<?php

namespace Oro\Bundle\CustomerBundle\EventListener;

use Oro\Bundle\NavigationBundle\Event\ConfigureMenuEvent;
use Oro\Bundle\NavigationBundle\Utils\MenuUpdateUtils;
use Oro\Bundle\SecurityBundle\SecurityFacade;

/**
 * Show/Hide menu item
 */
class NavigationListener
{
    const MENU_ITEM_ID = 'oro_customer_frontend_customer_user_address_index';

    /** @var SecurityFacade */
    private $securityFacade;

    /**
     * @param SecurityFacade $securityFacade
     */
    public function __construct(SecurityFacade $securityFacade)
    {
        $this->securityFacade = $securityFacade;
    }

    /**
     * @param ConfigureMenuEvent $event
     */
    public function onNavigationConfigure(ConfigureMenuEvent $event)
    {
        $addressBookItem = MenuUpdateUtils::findMenuItem($event->getMenu(), self::MENU_ITEM_ID);
        if ($addressBookItem !== null) {
            $isDisplay = false;
            if ($this->securityFacade->isGranted('oro_customer_frontend_customer_view') ||
                $this->securityFacade->isGranted('oro_customer_frontend_customer_user_view')) {
                $isDisplay = true;
            }
            $addressBookItem->setDisplay($isDisplay);
        }
    }
}
