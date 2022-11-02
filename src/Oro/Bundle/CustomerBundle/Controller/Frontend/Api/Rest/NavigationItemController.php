<?php

namespace Oro\Bundle\CustomerBundle\Controller\Frontend\Api\Rest;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\PinbarTab;
use Oro\Bundle\NavigationBundle\Controller\Api\NavigationItemController as BaseNavigationItemController;

/**
 * REST API controller to manage navigation items on the storefront.
 */
class NavigationItemController extends BaseNavigationItemController
{
    protected function getPinbarTabClass(): string
    {
        return PinbarTab::class;
    }

    protected function getUserClass(): string
    {
        return CustomerUser::class;
    }
}
