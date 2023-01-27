<?php

namespace Oro\Bundle\CommerceMenuBundle\MenuUpdate\Propagator\ToMenuItem;

use Knp\Menu\ItemInterface;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\NavigationBundle\Entity\MenuUpdateInterface;
use Oro\Bundle\NavigationBundle\MenuUpdate\Propagator\ToMenuItem\MenuUpdateToMenuItemPropagatorInterface;
use Oro\Bundle\NavigationBundle\MenuUpdate\Propagator\ToMenuUpdate\MenuItemToMenuUpdatePropagatorInterface;

/**
 * Propagates menu update data as extra data to menu item.
 */
class ExtrasPropagator implements MenuUpdateToMenuItemPropagatorInterface
{
    public function isApplicable(ItemInterface $menuItem, MenuUpdateInterface $menuUpdate, string $strategy): bool
    {
        return $menuUpdate instanceof MenuUpdate
            && in_array(
                $strategy,
                [
                    MenuItemToMenuUpdatePropagatorInterface::STRATEGY_BASIC,
                    MenuItemToMenuUpdatePropagatorInterface::STRATEGY_FULL
                ],
                true
            );
    }

    public function propagateFromMenuUpdate(
        ItemInterface $menuItem,
        MenuUpdateInterface $menuUpdate,
        string $strategy
    ): void {
        if (!$menuUpdate instanceof MenuUpdate) {
            return;
        }

        $menuItem->setExtra(MenuUpdate::IMAGE, $menuUpdate->getImage());
        $menuItem->setExtra(MenuUpdate::SCREENS, $menuUpdate->getScreens());
        $menuItem->setExtra(MenuUpdate::CONDITION, $menuUpdate->getCondition());
        $menuItem->setExtra(MenuUpdate::USER_AGENT_CONDITIONS, $menuUpdate->getMenuUserAgentConditions());

        $menuItem->setExtra(MenuUpdate::TARGET_CONTENT_NODE, $menuUpdate->getContentNode());
        $menuItem->setExtra(MenuUpdate::TARGET_CATEGORY, $menuUpdate->getCategory());
        $menuItem->setExtra(MenuUpdate::SYSTEM_PAGE_ROUTE, $menuUpdate->getSystemPageRoute());

        if ($menuUpdate->getMaxTraverseLevel() !== null) {
            $menuItem->setExtra(MenuUpdate::MAX_TRAVERSE_LEVEL, $menuUpdate->getMaxTraverseLevel());
        }

        if ($menuUpdate->getMenuTemplate() !== null) {
            $menuItem->setExtra(MenuUpdate::MENU_TEMPLATE, $menuUpdate->getMenuTemplate());
        }
    }
}
