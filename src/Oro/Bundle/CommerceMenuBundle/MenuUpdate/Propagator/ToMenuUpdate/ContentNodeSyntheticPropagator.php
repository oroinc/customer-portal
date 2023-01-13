<?php

namespace Oro\Bundle\CommerceMenuBundle\MenuUpdate\Propagator\ToMenuUpdate;

use Knp\Menu\ItemInterface;
use Oro\Bundle\CommerceMenuBundle\Builder\ContentNodeTreeBuilder;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\NavigationBundle\Entity\MenuUpdateInterface;
use Oro\Bundle\NavigationBundle\MenuUpdate\Propagator\ToMenuUpdate\MenuItemToMenuUpdatePropagatorInterface;
use Oro\Bundle\WebCatalogBundle\Entity\ContentNode;

/**
 * Marks menu update as synthetic if it is a content node menu item that is moved out from its parent.
 */
class ContentNodeSyntheticPropagator implements MenuItemToMenuUpdatePropagatorInterface
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
            || $menuItem->getExtra(ContentNodeTreeBuilder::IS_TREE_ITEM) === true) {
            /** @var ContentNode $contentNode */
            $contentNode = $menuItem->getExtra(MenuUpdate::TARGET_CONTENT_NODE);
            if ($contentNode instanceof ContentNode) {
                $parentMenuItem = $menuItem->getParent();
                if ($parentMenuItem) {
                    $parentMenuContentNode = $parentMenuItem->getExtra(MenuUpdate::TARGET_CONTENT_NODE);
                    if ($parentMenuContentNode instanceof ContentNode) {
                        $parentTreeItemNamePrefix = ContentNodeTreeBuilder::getTreeItemNamePrefix(
                            $parentMenuItem,
                            $parentMenuContentNode->getId()
                        );

                        if ($menuItem->getName() !== $parentTreeItemNamePrefix . $contentNode->getId()) {
                            $menuUpdate->setSynthetic(true);
                        } else {
                            $parentContentNode = $contentNode->getParentNode();
                            $menuUpdate->setSynthetic($parentContentNode?->getId() !== $parentMenuContentNode->getId());
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
