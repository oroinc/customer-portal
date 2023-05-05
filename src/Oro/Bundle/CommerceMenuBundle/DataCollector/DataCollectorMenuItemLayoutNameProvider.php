<?php

declare(strict_types=1);

namespace Oro\Bundle\CommerceMenuBundle\DataCollector;

use Knp\Menu\ItemInterface;
use Oro\Bundle\LayoutBundle\DataCollector\DataCollectorLayoutNameProviderInterface;
use Oro\Component\Layout\ContextInterface;

/**
 * Provides the layout name for data collector taking into account menu_item context data.
 */
class DataCollectorMenuItemLayoutNameProvider implements DataCollectorLayoutNameProviderInterface
{
    public function getNameByContext(ContextInterface $context): string
    {
        $menuItemName = $context->getOr('menu_item_name') ;
        $menuItem = $context->data()->has('menu_item') ? $context->data()->get('menu_item') : null;
        if ($menuItemName !== null && $menuItem instanceof ItemInterface) {
            return 'Menu Item: ' . $menuItem->getLabel();
        }

        return '';
    }
}
