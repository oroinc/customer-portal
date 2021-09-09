<?php

namespace Oro\Bundle\WebsiteBundle\Resolver;

use Oro\Bundle\CacheBundle\Provider\MemoryCacheProviderAwareTrait;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\EmailBundle\Provider\UrlProviderTrait;
use Oro\Component\Website\WebsiteInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Provides website URL/secure URL based on configuration settings (application URL) as well as
 * generated value by UrlGenerator.
 */
class WebsiteUrlResolver
{
    use UrlProviderTrait;
    use MemoryCacheProviderAwareTrait;

    private const CONFIG_URL        = 'oro_website.url';
    private const CONFIG_SECURE_URL = 'oro_website.secure_url';

    /** @var ConfigManager */
    protected $configManager;

    public function __construct(ConfigManager $configManager, UrlGeneratorInterface $urlGenerator)
    {
        $this->configManager = $configManager;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param WebsiteInterface|null $website
     * @param bool                  $clearUrl
     *
     * @return string|null
     */
    public function getWebsiteUrl(WebsiteInterface $website = null, bool $clearUrl = false)
    {
        $cacheKey = 'url_' . ($website ? $website->getId() : 0);

        return $this->getMemoryCacheProvider()->get($cacheKey, function () use ($website, $clearUrl) {
            $url = $this->configManager->get(self::CONFIG_URL, false, false, $website);
            if ($url && $clearUrl) {
                $url = $this->getCleanUrl($url);
            }

            return $url;
        });
    }

    /**
     * @param WebsiteInterface|null $website
     * @param bool                  $clearUrl
     *
     * @return string|null
     */
    public function getWebsiteSecureUrl(WebsiteInterface $website = null, bool $clearUrl = false)
    {
        $cacheKey = 'secure_url_' . ($website ? $website->getId() : 0);

        return $this->getMemoryCacheProvider()->get($cacheKey, function () use ($website, $clearUrl) {
            $url = $this->getSecureUrl($website);
            if ($url && $clearUrl) {
                $url = $this->getCleanUrl($url);
            }

            return $url;
        });
    }

    /**
     * @param string                $route
     * @param array                 $routeParams
     * @param WebsiteInterface|null $website
     *
     * @return string
     */
    public function getWebsitePath(string $route, array $routeParams, WebsiteInterface $website = null)
    {
        return $this->preparePath($this->getWebsiteUrl($website), $route, $routeParams);
    }

    /**
     * @param string                $route
     * @param array                 $routeParams
     * @param WebsiteInterface|null $website
     *
     * @return string
     */
    public function getWebsiteSecurePath(string $route, array $routeParams, WebsiteInterface $website = null)
    {
        return $this->preparePath($this->getWebsiteSecureUrl($website), $route, $routeParams);
    }

    /**
     * @param WebsiteInterface|null $website
     *
     * @return string|null
     */
    protected function getSecureUrl(WebsiteInterface $website = null)
    {
        $url = $this->getWebsiteScopeConfigValue(self::CONFIG_SECURE_URL, $website);
        if ($url) {
            return $url;
        }
        $url = $this->getWebsiteScopeConfigValue(self::CONFIG_URL, $website);
        if ($url) {
            return $url;
        }
        $url = $this->getDefaultConfigValue(self::CONFIG_SECURE_URL, $website);
        if ($url) {
            return $url;
        }

        return $this->getDefaultConfigValue(self::CONFIG_URL, $website);
    }

    /**
     * @param string $url
     *
     * @return string
     */
    protected function getCleanUrl($url)
    {
        return rtrim(explode('?', $url)[0], '/');
    }

    /**
     * @param string                $configKey
     * @param WebsiteInterface|null $website
     *
     * @return string|null
     */
    protected function getWebsiteScopeConfigValue($configKey, WebsiteInterface $website = null)
    {
        $configValue = $this->configManager->get($configKey, false, true, $website);
        if (!empty($configValue['value']) && empty($configValue['use_parent_scope_value'])) {
            return $configValue['value'];
        }

        return null;
    }

    /**
     * @param string                $configKey
     * @param WebsiteInterface|null $website
     *
     * @return string|null
     */
    protected function getDefaultConfigValue($configKey, WebsiteInterface $website = null)
    {
        return $this->configManager->get($configKey, true, false, $website);
    }
}
