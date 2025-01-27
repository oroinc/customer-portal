<?php

namespace Oro\Bundle\AddressValidationBundle\Cache;

use Oro\Bundle\AddressValidationBundle\Client\Response\AddressValidationResponseInterface;
use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Abstract cache adapter for storing responses from Address Validation Resolver service.
 */
abstract class AbstractAddressValidationResponseCache implements AddressValidationResponseCacheInterface
{
    protected const int LIFETIME = 86400;

    public function __construct(
        protected CacheItemPoolInterface $cache
    ) {
    }

    public function has(AddressValidationCacheKeyInterface $key): bool
    {
        return $this->cache->getItem($this->generateCacheKey($key))->isHit();
    }

    public function get(AddressValidationCacheKeyInterface $key): ?AddressValidationResponseInterface
    {
        $cacheKey = $this->cache->getItem($this->generateCacheKey($key));

        return $cacheKey->isHit() ? $cacheKey->get() : null;
    }

    public function set(
        AddressValidationCacheKeyInterface $key,
        AddressValidationResponseInterface $response
    ): bool {
        $cacheItem = $this->cache->getItem($this->generateCacheKey($key));
        $cacheItem->expiresAfter($this->getExpiresAfter($key->getTransport()))->set($response);

        return $this->cache->save($cacheItem);
    }

    public function delete(AddressValidationCacheKeyInterface $key): bool
    {
        return $this->cache->deleteItem($this->generateCacheKey($key));
    }

    public function deleteAll(): bool
    {
        return $this->cache->clear();
    }

    private function getExpiresAfter(Transport $transport): int
    {
        $interval = 0;

        $invalidateAt = $this->getInvalidatedAt($transport);
        if ($invalidateAt) {
            $interval = $invalidateAt->getTimestamp() - time();
        }

        if ($interval <= 0) {
            $interval = static::LIFETIME;
        }

        return $interval;
    }

    abstract protected function getInvalidatedAt(Transport $transport): ?\DateTimeInterface;

    abstract protected function generateCacheKey(AddressValidationCacheKeyInterface $key): string;
}
