<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Data\ORM;

use Oro\Bundle\CustomerBundle\Entity\NavigationItem;
use Oro\Bundle\CustomerBundle\Entity\PinbarTab;
use Oro\Bundle\NavigationBundle\Migrations\Data\ORM\UpdatePinbarTabUrlsAndTitles as ParentUpdatePinbarTabUrlsAndTitles;

/**
 * Updates PinbarTabs title and titleShort properties with actual titles.
 */
class UpdatePinbarTabUrlsAndTitles extends ParentUpdatePinbarTabUrlsAndTitles
{
    /**
     * {@inheritdoc}
     */
    protected function getPinbarTabClass()
    {
        return PinbarTab::class;
    }

    /**
     * {@inheritdoc}
     */
    protected function getNavigationItemClass()
    {
        return NavigationItem::class;
    }
}
