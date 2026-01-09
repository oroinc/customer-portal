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

    #[\Override]
    public function isOptional(): bool
    {
        return false;
    }

    #[\Override]
    public function warmUp(string $cacheDir, ?string $buildDir = null): array
    {
        $this->ownerTreeProvider->clearCache();
        return [];
    }
}
