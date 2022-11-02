<?php

namespace Oro\Bundle\CommerceMenuBundle\EventListener;

use Oro\Bundle\NavigationBundle\Event\ConfigureMenuEvent;
use Oro\Bundle\NavigationBundle\Utils\MenuUpdateUtils;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Menu listener that checks permissions for "menu_list_frontend" menu item.
 * For this item should be checked two ACL resources ("oro_config_system" and "oro_navigation_manage_menus").
 * That is why it is not possible to add this check into navigation.yml file where this menu item was described.
 */
class MenuListFrontendItemNavigationListener
{
    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var TokenAccessorInterface */
    private $tokenAccessor;

    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        TokenAccessorInterface $tokenAccessor
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenAccessor = $tokenAccessor;
    }

    public function onNavigationConfigure(ConfigureMenuEvent $event)
    {
        if (!$this->tokenAccessor->hasUser()) {
            return;
        }

        $menuListFrontendItem = MenuUpdateUtils::findMenuItem($event->getMenu(), 'menu_list_frontend');
        if (null !== $menuListFrontendItem
            && (
                !$this->authorizationChecker->isGranted('oro_config_system')
                || !$this->authorizationChecker->isGranted('oro_navigation_manage_menus')
            )
        ) {
            $menuListFrontendItem->setDisplay(false);
        }
    }
}
