<?php

namespace Oro\Bundle\CustomerBundle\EventListener;

use Oro\Bundle\NavigationBundle\Event\ConfigureMenuEvent;
use Oro\Bundle\NavigationBundle\Utils\MenuUpdateUtils;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Show/Hide "Address book" menu item.
 */
class NavigationListener
{
    const MENU_ITEM_ID = 'oro_customer_frontend_customer_user_address_index';

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public function onNavigationConfigure(ConfigureMenuEvent $event)
    {
        $addressBookItem = MenuUpdateUtils::findMenuItem($event->getMenu(), self::MENU_ITEM_ID);
        if ($addressBookItem !== null) {
            $isDisplay = false;
            if ($this->authorizationChecker->isGranted('oro_customer_frontend_customer_address_view') ||
                $this->authorizationChecker->isGranted('oro_customer_frontend_customer_user_address_view')) {
                $isDisplay = true;
            }
            $addressBookItem->setDisplay($isDisplay);
        }
    }
}
