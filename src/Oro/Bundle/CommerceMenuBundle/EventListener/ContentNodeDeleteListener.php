<?php

namespace Oro\Bundle\CommerceMenuBundle\EventListener;

use Symfony\Contracts\Cache\CacheInterface;

/**
 * Deletes menuUpdate query cache when ContentNode is deleted.
 */
class ContentNodeDeleteListener
{
    private CacheInterface $cacheProvider;

    public function __construct(CacheInterface $cacheProvider)
    {
        $this->cacheProvider = $cacheProvider;
    }

    public function postRemove(): void
    {
        $this->cacheProvider->clear();
    }
}
