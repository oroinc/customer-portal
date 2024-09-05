<?php

namespace Oro\Bundle\CommerceMenuBundle\EventListener;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\Proxy;
use Oro\Bundle\AttachmentBundle\Entity\File;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\NavigationBundle\Event\MenuUpdatesApplyAfterEvent;

/**
 * Loads images for all menu items to avoid loading each image by a separate DB query.
 */
class LoadMenuImagesListener
{
    public function __construct(
        private ManagerRegistry $doctrine
    ) {
    }

    public function onMenuUpdatesApplyAfter(MenuUpdatesApplyAfterEvent $event): void
    {
        $menuItems = $event->getContext()->getMenuItemsByName();
        if (!$menuItems) {
            return;
        }

        $imageIds = [];
        foreach ($menuItems as $menuItem) {
            $image = $menuItem->getExtra(MenuUpdate::IMAGE);
            if ($image instanceof Proxy && !$image->__isInitialized() && $image instanceof File) {
                $imageIds[] = $image->getId();
            }
        }
        if (!$imageIds) {
            return;
        }

        $imageIds = array_unique($imageIds);
        sort($imageIds);
        // load entities into the entity manager
        $this->doctrine->getRepository(File::class)->findBy(['id' => $imageIds]);
    }
}
