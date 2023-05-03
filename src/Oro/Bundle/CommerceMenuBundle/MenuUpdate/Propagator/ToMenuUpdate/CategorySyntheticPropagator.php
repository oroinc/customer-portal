<?php

namespace Oro\Bundle\CommerceMenuBundle\MenuUpdate\Propagator\ToMenuUpdate;

use Knp\Menu\ItemInterface;
use Oro\Bundle\CatalogBundle\Entity\Category;
use Oro\Bundle\CommerceMenuBundle\Builder\CategoryTreeBuilder;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\NavigationBundle\Entity\MenuUpdateInterface;
use Oro\Bundle\NavigationBundle\MenuUpdate\Propagator\ToMenuUpdate\MenuItemToMenuUpdatePropagatorInterface;

/**
 * Marks menu update as synthetic if it is a category tree menu item that is moved out from its parent.
 */
class CategorySyntheticPropagator implements MenuItemToMenuUpdatePropagatorInterface
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
            || $menuItem->getExtra(CategoryTreeBuilder::IS_TREE_ITEM) === true) {
            /** @var Category $category */
            $category = $menuItem->getExtra(MenuUpdate::TARGET_CATEGORY);
            if ($category instanceof Category) {
                $parentMenuItem = $menuItem->getParent();
                if ($parentMenuItem) {
                    $parentMenuCategory = $parentMenuItem->getExtra(MenuUpdate::TARGET_CATEGORY);
                    if ($parentMenuCategory instanceof Category) {
                        $parentTreeItemNamePrefix = CategoryTreeBuilder::getTreeItemNamePrefix(
                            $parentMenuItem,
                            $parentMenuCategory->getId()
                        );

                        if ($menuItem->getName() !== $parentTreeItemNamePrefix . $category->getId()) {
                            $menuUpdate->setSynthetic(true);
                        } else {
                            $parentContentNode = $category->getParentCategory();
                            $menuUpdate->setSynthetic($parentContentNode?->getId() !== $parentMenuCategory->getId());
                        }
                    } else {
                        $menuUpdate->setSynthetic(true);
                    }
                } else {
                    $menuUpdate->setSynthetic(true);
                }
            }
        }
    }
}
