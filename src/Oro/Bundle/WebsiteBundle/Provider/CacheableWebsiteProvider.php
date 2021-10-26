<?php

namespace Oro\Bundle\WebsiteBundle\Provider;

use Doctrine\Common\Cache\CacheProvider;
use Oro\Bundle\SecurityBundle\Authentication\Token\OrganizationAwareTokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * The provider that uses a cache to prevent unneeded loading of website identifiers from the database.
 */
class CacheableWebsiteProvider implements WebsiteProviderInterface
{
    private const WEBSITE_CACHE_KEY = 'oro_website';

    /** @var WebsiteProviderInterface */
    private $websiteProvider;

    /** @var CacheProvider */
    private $cacheProvider;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(
        WebsiteProviderInterface $websiteProvider,
        CacheProvider $cacheProvider,
        TokenStorageInterface $tokenStorage
    ) {
        $this->websiteProvider = $websiteProvider;
        $this->cacheProvider = $cacheProvider;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function getWebsites()
    {
        $cacheKey = $this->getCacheKey('entities');
        $websites = $this->cacheProvider->fetch($cacheKey);
        if (false === $websites) {
            $websites = $this->websiteProvider->getWebsites();
            $this->cacheProvider->save($cacheKey, $websites);
        }

        return $websites;
    }

    /**
     * {@inheritdoc}
     */
    public function getWebsiteIds()
    {
        $cacheKey = $this->getCacheKey('ids');
        $websiteIds = $this->cacheProvider->fetch($cacheKey);
        if (false === $websiteIds) {
            $websiteIds = $this->websiteProvider->getWebsiteIds();
            $this->cacheProvider->save($cacheKey, $websiteIds);
        }

        return $websiteIds;
    }

    /**
     * {@inheritdoc}
     */
    public function getWebsiteChoices()
    {
        $websiteChoices = [];
        foreach ($this->getWebsites() as $website) {
            $websiteChoices[$website->getName()] = $website->getId();
        }

        return $websiteChoices;
    }

    /**
     * Removes all data from the internal cache.
     */
    public function clearCache()
    {
        $this->cacheProvider->deleteAll();
    }

    private function getCacheKey(string $postfix): string
    {
        return self::WEBSITE_CACHE_KEY . '_' . $this->getOrganizationId() . '_' . $postfix;
    }

    /**
     * @return int|string
     */
    private function getOrganizationId()
    {
        $token = $this->tokenStorage->getToken();
        if ($token instanceof OrganizationAwareTokenInterface) {
            return $token->getOrganization()->getId();
        }

        return 'all';
    }
}
