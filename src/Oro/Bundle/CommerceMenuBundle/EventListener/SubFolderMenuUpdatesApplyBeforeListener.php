<?php

namespace Oro\Bundle\CommerceMenuBundle\EventListener;

use Oro\Bundle\CommerceMenuBundle\Handler\SubFolderUriHandler;
use Oro\Bundle\NavigationBundle\Event\MenuUpdatesApplyBeforeEvent;

/**
 * Adds subfolder to the updated menu items uri
 */
class SubFolderMenuUpdatesApplyBeforeListener
{
    public function __construct(private SubFolderUriHandler $uriHandler)
    {
    }

    public function onMenuUpdatesApplyBefore(MenuUpdatesApplyBeforeEvent $event): void
    {
        if (!$this->uriHandler->hasSubFolder()) {
            return;
        }

        if ($menuUpdates = $event->getMenuUpdates()) {
            foreach ($menuUpdates as $menuUpdate) {
                $menuUpdate->setUri($this->uriHandler->handle((string) $menuUpdate->getUri()));
            }
        }
    }
}
