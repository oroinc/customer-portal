<?php

namespace Oro\Bundle\CustomerBundle\Acl\Cache;

use Oro\Bundle\CacheBundle\Generator\UniversalCacheKeyGenerator;
use Psr\Cache\CacheItemPoolInterface;

/**
 * This cache stores calculated ACL data for customer visitors per website.
 */
class CustomerVisitorAclCache
{
    private CacheItemPoolInterface $cache;

    private array $cachedData = [];

    public function __construct(CacheItemPoolInterface $cache)
    {
        $this->cache = $cache;
    }

    public function cacheAclResult(
        int $websiteId,
        ?string $subjectName,
        array $attributes,
        int $voteResult
    ): void {
        $cacheKey = $this->getCacheKey($websiteId);
        $itemId = $this->getItemKey($subjectName, $attributes);

        $cachedItem = $this->cache->getItem($cacheKey);
        $this->cachedData[$websiteId] = $cachedItem->isHit() ? $cachedItem->get() : [];

        $this->cachedData[$websiteId][$itemId] = $voteResult;
        $cachedItem->set($this->cachedData[$websiteId]);
        $this->cache->save($cachedItem);
    }

    public function isVoteResultExist(int $websiteId, ?string $subjectName, array $attributes): bool
    {
        $itemId = $this->getItemKey($subjectName, $attributes);
        $this->makeSureDataIsLoaded($websiteId);

        return isset($this->cachedData[$websiteId][$itemId]);
    }

    public function getVoteResult(int $websiteId, ?string $subjectName, array $attributes): int
    {
        $itemId = $this->getItemKey($subjectName, $attributes);
        $this->makeSureDataIsLoaded($websiteId);

        if (!isset($this->cachedData[$websiteId][$itemId])) {
            throw new \RuntimeException('Customer visitor ACL cache have no data for given arguments.'
                . ' Please use isVoteResultExist method to check if data exist.');
        }

        return $this->cachedData[$websiteId][$itemId];
    }

    public function clearWebsiteData(int $websiteId): void
    {
        $cacheKey = $this->getCacheKey($websiteId);
        if (isset($this->cachedData[$websiteId])) {
            unset($this->cachedData[$websiteId]);
        }
        if ($this->cache->hasItem($cacheKey)) {
            $this->cache->deleteItem($cacheKey);
        }
    }

    public function clearCache(): void
    {
        $this->cache->clear();
        $this->cachedData = [];
    }

    private function getItemKey(?string $subjectName, array $attributes): string
    {
        return $subjectName . '@' . implode('@', $attributes);
    }

    private function getCacheKey(int $websiteId): string
    {
        return UniversalCacheKeyGenerator::normalizeCacheKey('website_' . $websiteId);
    }

    private function makeSureDataIsLoaded(int $websiteId): void
    {
        if (!\array_key_exists($websiteId, $this->cachedData)) {
            $cacheKey = $this->getCacheKey($websiteId);
            $cachedItem = $this->cache->getItem($cacheKey);
            $this->cachedData[$websiteId] = $cachedItem->isHit() ? $cachedItem->get() : [];
        }
    }
}
