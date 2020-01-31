<?php

namespace Oro\Bundle\CustomerBundle\Controller\Frontend\Api\Rest;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserSidebarState;
use Oro\Bundle\SidebarBundle\Controller\Api\Rest\SidebarController as BaseController;

/**
 * Provides REST API for the sidebar on the storefront.
 *
 * @RouteResource("sidebars")
 * @NamePrefix("oro_api_frontend_")
 */
class SidebarController extends BaseController
{
    /**
     * @return string
     */
    protected function getSidebarStateClass()
    {
        return CustomerUserSidebarState::class;
    }
}
