<?php

namespace Oro\Bundle\CommerceMenuBundle\EventListener;

use Oro\Bundle\CommerceMenuBundle\Handler\SubFolderUriHandler;
use Oro\Bundle\NavigationBundle\Event\MenuUpdatesApplyAfterEvent;

/**
 * Adds subfolder to the updated menu items uri
 */
class SubFolderMenuUpdatesApplyAfterListener
{
    public function __construct(
        private SubFolderUriHandler $uriHandler
    ) {
    }

    public function onMenuUpdatesApplyAfter(MenuUpdatesApplyAfterEvent $event): void
    {
        if (!$this->uriHandler->hasSubFolder()) {
            return;
        }

        $menuItems = $event->getContext()->getMenuItemsByName();
        if ($menuItems) {
            foreach ($menuItems as $menuItem) {
                $menuItem->setUri($this->uriHandler->handle((string) $menuItem->getUri()));
            }
        }
    }
}
