<?php

namespace Oro\Bundle\CustomerBundle\Entity\Repository;

use Oro\Bundle\CustomerBundle\Entity\NavigationItem;
use Oro\Bundle\NavigationBundle\Entity\Repository\PinbarTabRepository as BasePinbarTabRepository;

/**
 *  PinbarTab Repository
 */
class PinbarTabRepository extends BasePinbarTabRepository
{
    /**
     * {@inheritdoc}
     */
    protected function getNavigationItemClassName()
    {
        return NavigationItem::class;
    }
}
