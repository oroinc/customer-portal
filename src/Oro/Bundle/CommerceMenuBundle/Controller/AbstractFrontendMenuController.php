<?php

namespace Oro\Bundle\CommerceMenuBundle\Controller;

use Oro\Bundle\NavigationBundle\Controller\AbstractMenuController;
use Oro\Bundle\NavigationBundle\Manager\MenuUpdateManager;

abstract class AbstractFrontendMenuController extends AbstractMenuController
{
    /**
     * @return MenuUpdateManager
     */
    protected function getMenuUpdateManager()
    {
        return $this->get('oro_commerce_menu.manager.menu_update');
    }

    /**
     * @return string
     */
    protected function getSavedSuccessMessage()
    {
        return $this->get('translator')->trans('oro.commercemenu.menuupdate.saved_message');
    }
}
