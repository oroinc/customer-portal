<?php

namespace Oro\Bundle\CustomerBundle\Controller\Frontend\Api\Rest;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use Oro\Bundle\SidebarBundle\Controller\Api\Rest\SidebarController as BaseController;

/**
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
        return $this->getParameter('oro_customer.entity.sidebar_state.class');
    }
}
