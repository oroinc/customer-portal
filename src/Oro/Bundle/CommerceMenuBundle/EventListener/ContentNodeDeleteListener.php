<?php

namespace Oro\Bundle\CommerceMenuBundle\EventListener;

use Doctrine\Common\Cache\CacheProvider;

/**
 * Deletes menuUpdate query cache when ContentNode is deleted.
 */
class ContentNodeDeleteListener
{
    /** @var CacheProvider */
    private $cacheProvider;

    public function __construct(CacheProvider $cacheProvider)
    {
        $this->cacheProvider = $cacheProvider;
    }

    public function postRemove(): void
    {
        $this->cacheProvider->deleteAll();
    }
}
