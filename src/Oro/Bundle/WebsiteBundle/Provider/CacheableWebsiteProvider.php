<?php

namespace Oro\Bundle\WebsiteBundle\Provider;

use Oro\Bundle\SecurityBundle\Authentication\Token\OrganizationAwareTokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * The provider that uses a cache to prevent unneeded loading of website identifiers from the database.
 */
class CacheableWebsiteProvider implements WebsiteProviderInterface
{
    private const WEBSITE_CACHE_KEY = 'oro_website';

    private WebsiteProviderInterface $websiteProvider;
    private CacheInterface $cacheProvider;
    private TokenStorageInterface $tokenStorage;

    public function __construct(
        WebsiteProviderInterface $websiteProvider,
        CacheInterface $cacheProvider,
        TokenStorageInterface $tokenStorage
    ) {
        $this->websiteProvider = $websiteProvider;
        $this->cacheProvider = $cacheProvider;
        $this->tokenStorage = $tokenStorage;
    }

    public function getWebsites(): array
    {
        return $this->cacheProvider->get($this->getCacheKey('entities'), function () {
            return $this->websiteProvider->getWebsites();
        });
    }

    public function getWebsiteIds(): array
    {
        return $this->cacheProvider->get($this->getCacheKey('ids'), function () {
            return $this->websiteProvider->getWebsiteIds();
        });
    }

    public function getWebsiteChoices(): array
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
    public function clearCache(): void
    {
        $this->cacheProvider->clear();
    }

    private function getCacheKey(string $postfix): string
    {
        return self::WEBSITE_CACHE_KEY . '_' . $this->getOrganizationId() . '_' . $postfix;
    }

    private function getOrganizationId(): int|string
    {
        $token = $this->tokenStorage->getToken();
        if ($token instanceof OrganizationAwareTokenInterface) {
            return $token->getOrganization()->getId();
        }

        return 'all';
    }
}
