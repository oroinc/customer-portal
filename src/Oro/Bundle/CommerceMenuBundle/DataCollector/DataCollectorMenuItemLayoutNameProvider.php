<?php

declare(strict_types=1);

namespace Oro\Bundle\CommerceMenuBundle\DataCollector;

use Oro\Bundle\LayoutBundle\DataCollector\DataCollectorLayoutNameProviderInterface;
use Oro\Component\Layout\ContextInterface;

/**
 * Provides the layout name for data collector taking into account menu_item_name context var.
 */
class DataCollectorMenuItemLayoutNameProvider implements DataCollectorLayoutNameProviderInterface
{
    public function getNameByContext(ContextInterface $context): string
    {
        $menuItemName = $context->getOr('menu_item_name');
        if ($menuItemName) {
            return 'Menu Item: ' . $menuItemName;
        }

        return '';
    }
}
