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
    /** @var WebsiteManager */
    private $websiteManager;

    /** @var bool */
    private $enabled = false;

    /** @var bool */
    private $stubbingSetCurrentWebsiteEnabled = false;

    /** @var Website|null */
    private $stubCurrentWebsite;

    /** @var Website|null */
    private $stubDefaultWebsite;

    /** @var CacheInterface */
    private $cacheProvider;

    public function __construct(WebsiteManager $websiteManager, CacheInterface $cacheProvider)
    {
        $this->cacheProvider = $cacheProvider;
        $this->websiteManager = $websiteManager;
    }

    public function enableStub()
    {
        $this->enabled = true;
    }

    public function enableSetCurrentWebsiteStubbing()
    {
        $this->stubbingSetCurrentWebsiteEnabled = true;
    }

    public function disableStub()
    {
        $this->enabled = false;
        $this->stubbingSetCurrentWebsiteEnabled = false;
        $this->stubCurrentWebsite = null;
        $this->stubDefaultWebsite = null;
        $this->cacheProvider->clear();
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentWebsite()
    {
        if ($this->enabled) {
            return $this->stubCurrentWebsite;
        }

        return $this->websiteManager->getCurrentWebsite();
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultWebsite()
    {
        if ($this->enabled) {
            return $this->stubDefaultWebsite;
        }

        return $this->websiteManager->getDefaultWebsite();
    }

    public function setCurrentWebsiteStub(Website $currentWebsite = null)
    {
        if (!$this->enabled) {
            $this->enableStub();
        }
        $this->stubCurrentWebsite = $currentWebsite;
    }

    public function setDefaultWebsiteStub(Website $defaultWebsite = null)
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

    /**
     * @param string $method
     * @param array  $args
     *
     * @return mixed
     */
    public function __call($method, $args)
    {
        return call_user_func_array([$this->websiteManager, $method], $args);
    }
}
