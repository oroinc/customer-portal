<?php

namespace Oro\Bundle\CustomerBundle\Cache;

use Oro\Bundle\SecurityBundle\Owner\OwnerTreeProviderInterface;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmer;

/**
 * Clear owners cache on warmup.
 */
class OwnerCacheWarmer extends CacheWarmer
{
    public function __construct(
        protected OwnerTreeProviderInterface $ownerTreeProvider
    ) {
    }

    public function isOptional()
    {
        return false;
    }

    public function warmUp(string $cacheDir)
    {
        $this->ownerTreeProvider->clearCache();
    }
}
