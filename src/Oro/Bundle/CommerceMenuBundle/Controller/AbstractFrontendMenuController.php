<?php

namespace Oro\Bundle\CommerceMenuBundle\Controller;

use Oro\Bundle\NavigationBundle\Controller\AbstractMenuController;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Abstract controller for the frontend menu.
 */
abstract class AbstractFrontendMenuController extends AbstractMenuController
{
    protected function getSavedSuccessMessage(): string
    {
        return $this->container->get(TranslatorInterface::class)->trans('oro.commercemenu.menuupdate.saved_message');
    }
}
