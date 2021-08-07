<?php

namespace Oro\Bundle\CustomerBundle\Controller\Frontend\Api\Rest;

use Oro\Bundle\CustomerBundle\Entity\PageState;
use Oro\Bundle\NavigationBundle\Controller\Api\PagestateController as BasePagestateController;

/**
 * REST API CRUD controller for PageState entity on the storefront.
 */
class PagestateController extends BasePagestateController
{
    protected function getPageStateClass(): string
    {
        return PageState::class;
    }
}
