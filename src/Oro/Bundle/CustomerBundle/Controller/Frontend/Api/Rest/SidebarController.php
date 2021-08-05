<?php

namespace Oro\Bundle\CustomerBundle\Controller\Frontend\Api\Rest;

use Oro\Bundle\CustomerBundle\Entity\CustomerUserSidebarState;
use Oro\Bundle\SidebarBundle\Controller\Api\Rest\SidebarController as BaseController;

/**
 * REST API controller for the sidebar on the storefront.
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
