<?php

namespace Oro\Bundle\CustomerBundle\Controller\Frontend\Api\Rest;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use Oro\Bundle\CustomerBundle\Entity\PageState;
use Oro\Bundle\NavigationBundle\Controller\Api\PagestateController as BasePagestateController;

/**
 * Provides REST API CRUD actions for PageState entity on the storefront.
 *
 * @NamePrefix("oro_api_frontend_")
 */
class PagestateController extends BasePagestateController
{
    /**
     * @return string
     */
    protected function getPageStateClass()
    {
        return PageState::class;
    }
}
