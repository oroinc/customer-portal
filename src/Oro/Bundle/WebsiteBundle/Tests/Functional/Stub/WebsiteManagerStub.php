<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Functional\Stub;

use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * The decorator for WebsiteManager that allows to substitute
 * the default website and the current website in functional tests.
 */
class WebsiteManagerStub extends WebsiteManager
{
    private WebsiteManager $websiteManager;
    private CacheInterface $cacheProvider;
    private bool $enabled = false;
    private bool $stubbingSetCurrentWebsiteEnabled = false;
    private ?Website $stubCurrentWebsite = null;
    private ?Website $stubDefaultWebsite = null;

    public function __construct(WebsiteManager $websiteManager, CacheInterface $cacheProvider)
    {
        $this->cacheProvider = $cacheProvider;
        $this->websiteManager = $websiteManager;
    }

    public function enableStub(): void
    {
        $this->enabled = true;
    }

    public function enableSetCurrentWebsiteStubbing(): void
    {
        $this->stubbingSetCurrentWebsiteEnabled = true;
    }

    public function disableStub(): void
    {
        $this->enabled = false;
        $this->stubbingSetCurrentWebsiteEnabled = false;
        $this->stubCurrentWebsite = null;
        $this->stubDefaultWebsite = null;
        $this->cacheProvider->clear();
    }

    /**
     * {@inheritDoc}
     */
    public function getCurrentWebsite(): ?Website
    {
        if ($this->enabled) {
            return $this->stubCurrentWebsite;
        }

        return $this->websiteManager->getCurrentWebsite();
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultWebsite(): ?Website
    {
        if ($this->enabled) {
            return $this->stubDefaultWebsite;
        }

        return $this->websiteManager->getDefaultWebsite();
    }

    public function setCurrentWebsiteStub(Website $currentWebsite = null): void
    {
        if (!$this->enabled) {
            $this->enableStub();
        }
        $this->stubCurrentWebsite = $currentWebsite;
    }

    public function setDefaultWebsiteStub(Website $defaultWebsite = null): void
    {
        if (!$this->enabled) {
            $this->enableStub();
        }
        $this->stubDefaultWebsite = $defaultWebsite;
    }

    public function setCurrentWebsite(?Website $currentWebsite): void
    {
        if ($this->stubbingSetCurrentWebsiteEnabled) {
            $this->setCurrentWebsiteStub($currentWebsite);
        }
        $this->websiteManager->setCurrentWebsite($currentWebsite);
    }

    public function __call(string $method, array $args): mixed
    {
        return \call_user_func_array([$this->websiteManager, $method], $args);
    }
}
