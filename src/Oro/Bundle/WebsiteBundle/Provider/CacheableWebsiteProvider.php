<?php

namespace Oro\Bundle\WebsiteBundle\Provider;

use Doctrine\Common\Cache\CacheProvider;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\SecurityBundle\Authentication\Token\OrganizationAwareTokenInterface;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * The provider that uses a cache to prevent unneeded loading of website identifiers from the database.
 */
class CacheableWebsiteProvider implements WebsiteProviderInterface
{
    const WEBSITE_IDS_CACHE_KEY = 'oro_website_entity_ids';

    /** @var WebsiteProviderInterface */
    private $websiteProvider;

    /** @var CacheProvider */
    private $cacheProvider;

    /** @var DoctrineHelper */
    private $doctrineHelper;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /**
     * @param WebsiteProviderInterface $websiteProvider
     * @param CacheProvider $cacheProvider
     * @param DoctrineHelper $doctrineHelper
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
        WebsiteProviderInterface $websiteProvider,
        CacheProvider $cacheProvider,
        DoctrineHelper $doctrineHelper,
        TokenStorageInterface $tokenStorage
    ) {
        $this->websiteProvider = $websiteProvider;
        $this->cacheProvider = $cacheProvider;
        $this->doctrineHelper = $doctrineHelper;
        $this->tokenStorage = $tokenStorage;
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
        $cacheKey = $this->getCacheKey();
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
        foreach ($this->getWebsiteIds() as $websiteId) {
            /** @var Website $website */
            $website = $this->doctrineHelper->getEntityReference(Website::class, $websiteId);
            $websiteChoices[$website->getName()] = $websiteId;
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

    /**
     * @return string
     */
    private function getCacheKey(): string
    {
        $token = $this->tokenStorage->getToken();
        $organizationId = $token instanceof OrganizationAwareTokenInterface
            ? $token->getOrganization()->getId()
            : 'all';

        return self::WEBSITE_IDS_CACHE_KEY . '_' . $organizationId;
    }
}
