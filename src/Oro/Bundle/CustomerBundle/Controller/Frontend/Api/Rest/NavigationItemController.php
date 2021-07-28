<?php

namespace Oro\Bundle\CustomerBundle\Controller\Frontend\Api\Rest;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\PinbarTab;
use Oro\Bundle\NavigationBundle\Controller\Api\NavigationItemController as BaseNavigationItemController;

/**
 * Provides API for managing navigation items on the storefront.
 *
 * @RouteResource("navigationitems")
 * @NamePrefix("oro_api_frontend_")
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
