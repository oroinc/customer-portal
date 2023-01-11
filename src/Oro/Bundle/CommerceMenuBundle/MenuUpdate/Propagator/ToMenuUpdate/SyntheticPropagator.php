<?php

namespace Oro\Bundle\CommerceMenuBundle\MenuUpdate\Propagator\ToMenuUpdate;

use Knp\Menu\ItemInterface;
use Oro\Bundle\CommerceMenuBundle\Builder\CategoryTreeBuilder;
use Oro\Bundle\CommerceMenuBundle\Builder\ContentNodeTreeBuilder;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\NavigationBundle\Entity\MenuUpdateInterface;
use Oro\Bundle\NavigationBundle\MenuUpdate\Propagator\ToMenuUpdate\MenuItemToMenuUpdatePropagatorInterface;

/**
 * Marks menu update as synthetic if it is a content node or category tree menu item that is moved out from its parent.
 */
class SyntheticPropagator implements MenuItemToMenuUpdatePropagatorInterface
{
    public function isApplicable(MenuUpdateInterface $menuUpdate, ItemInterface $menuItem, string $strategy): bool
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

    public function propagateFromMenuItem(
        MenuUpdateInterface $menuUpdate,
        ItemInterface $menuItem,
        string $strategy
    ): void {
        if (!$menuUpdate instanceof MenuUpdate) {
            return;
        }

        if ($menuItem->getExtra(MenuUpdateInterface::IS_SYNTHETIC) === true
            || $menuItem->getExtra(ContentNodeTreeBuilder::IS_TREE_ITEM) === true
            || $menuItem->getExtra(CategoryTreeBuilder::IS_TREE_ITEM) === true) {
            $menuUpdate->setSynthetic(
                $menuUpdate->getOriginKey() !== $menuItem->getParent()?->getName()
            );
        }
    }
}
