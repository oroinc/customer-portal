<?php

namespace Oro\Bundle\CustomerBundle\Controller\Frontend\Api\Rest;

use Oro\Bundle\CustomerBundle\Entity\CustomerUserSidebarWidget;
use Oro\Bundle\SidebarBundle\Controller\Api\Rest\WidgetController as BaseController;

/**
 * REST API controller to manage sidebar widgets on the storefront.
 */
class WidgetController extends BaseController
{
    /**
     * @return string
     */
    protected function getWidgetClass()
    {
        return CustomerUserSidebarWidget::class;
    }
}
