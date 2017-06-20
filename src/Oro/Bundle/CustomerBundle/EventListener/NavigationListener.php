<?php

namespace Oro\Bundle\CustomerBundle\EventListener;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

use Oro\Bundle\NavigationBundle\Event\ConfigureMenuEvent;
use Oro\Bundle\NavigationBundle\Utils\MenuUpdateUtils;

/**
 * Show/Hide menu item
 */
class NavigationListener
{
    const MENU_ITEM_ID = 'oro_customer_frontend_customer_user_address_index';

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /**
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @param ConfigureMenuEvent $event
     */
    public function onNavigationConfigure(ConfigureMenuEvent $event)
    {
        $addressBookItem = MenuUpdateUtils::findMenuItem($event->getMenu(), self::MENU_ITEM_ID);
        if ($addressBookItem !== null) {
            $isDisplay = false;
            if ($this->authorizationChecker->isGranted('oro_customer_frontend_customer_view') ||
                $this->authorizationChecker->isGranted('oro_customer_frontend_customer_user_view')) {
                $isDisplay = true;
            }
            $addressBookItem->setDisplay($isDisplay);
        }
    }
}
