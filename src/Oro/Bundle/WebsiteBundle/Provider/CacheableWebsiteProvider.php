<?php

namespace Oro\Bundle\WebsiteBundle\Provider;

use Doctrine\Common\Cache\CacheProvider;
use Oro\Bundle\WebsiteBundle\Provider\WebsiteProvider as BaseWebsiteProvider;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\WebsiteBundle\Entity\Website;

/**
 * The provider that uses a cache to prevent unneeded loading of website identifiers from the database.
 */
class CacheableWebsiteProvider extends BaseWebsiteProvider
{
    const WEBSITE_IDS_CACHE_KEY = 'oro_website_entity_ids';

    /** @var BaseWebsiteProvider */
    private $websiteProvider;

    /** @var CacheProvider */
    private $cacheProvider;

    /** @var DoctrineHelper */
    private $doctrineHelper;

    /**
     * @param BaseWebsiteProvider $websiteProvider
     * @param CacheProvider $cacheProvider
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(
        BaseWebsiteProvider $websiteProvider,
        CacheProvider $cacheProvider,
        DoctrineHelper $doctrineHelper
    ) {
        $this->websiteProvider = $websiteProvider;
        $this->cacheProvider = $cacheProvider;
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getWebsites()
    {
        $websites = [];
        foreach ($this->getWebsiteIds() as $websiteId) {
            $websites[$websiteId] = $this->doctrineHelper->getEntityReference(Website::class, $websiteId);
        }
        return $websites;
    }

    /**
     * {@inheritdoc}
     */
    public function getWebsiteIds()
    {
        $websiteIds = $this->cacheProvider->fetch(self::WEBSITE_IDS_CACHE_KEY);
        if (false === $websiteIds) {
            $websiteIds = $this->websiteProvider->getWebsiteIds();
            $this->cacheProvider->save(self::WEBSITE_IDS_CACHE_KEY, $websiteIds);
        }

        return $websiteIds;
    }

    /**
     * Checks if this provider has data in the internal cache.
     *
     * @return bool
     */
    public function hasCache()
    {
        return $this->cacheProvider->contains(self::WEBSITE_IDS_CACHE_KEY);
    }

    /**
     * Removes all data from the internal cache.
     */
    public function clearCache()
    {
        $this->cacheProvider->delete(self::WEBSITE_IDS_CACHE_KEY);
    }
}
