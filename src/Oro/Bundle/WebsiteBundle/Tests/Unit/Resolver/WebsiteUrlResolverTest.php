<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Unit\Resolver;

use Oro\Bundle\CacheBundle\Provider\MemoryCacheProviderInterface;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Resolver\WebsiteUrlResolver;
use Oro\Component\Testing\ReflectionUtil;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class WebsiteUrlResolverTest extends \PHPUnit\Framework\TestCase
{
    private const CONFIG_URL = 'oro_website.url';
    private const CONFIG_SECURE_URL = 'oro_website.secure_url';

    /** @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject */
    private $configManager;

    /** @var UrlGeneratorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $urlGenerator;

    /** @var MemoryCacheProviderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $memoryCacheProvider;

    /** @var WebsiteUrlResolver */
    private $websiteUrlResolver;

    #[\Override]
    protected function setUp(): void
    {
        $this->configManager = $this->createMock(ConfigManager::class);
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->memoryCacheProvider = $this->createMock(MemoryCacheProviderInterface::class);

        $this->websiteUrlResolver = new WebsiteUrlResolver(
            $this->configManager,
            $this->urlGenerator,
            $this->memoryCacheProvider
        );
    }

    private function getWebsite(int $id): Website
    {
        $website = new Website();
        ReflectionUtil::setId($website, $id);

        return $website;
    }

    public function testGetWebsiteUrl(): void
    {
        $url = 'http://global.website.url/';

        $website = $this->getWebsite(2);

        $this->memoryCacheProvider->expects(self::once())
            ->method('get')
            ->willReturnCallback(function ($arguments, $callable) {
                return $callable($arguments);
            });

        $this->configManager->expects(self::once())
            ->method('get')
            ->with(self::CONFIG_URL, false, false, $website)
            ->willReturn($url);

        self::assertSame($url, $this->websiteUrlResolver->getWebsiteUrl($website));
    }

    public function testGetWebsiteUrlWhenDataCached(): void
    {
        $url = 'http://global.website.url/';

        $website = $this->getWebsite(2);

        $this->configManager->expects(self::never())
            ->method('get');

        $this->memoryCacheProvider->expects(self::once())
            ->method('get')
            ->willReturnCallback(function () use ($url) {
                return $url;
            });

        self::assertSame($url, $this->websiteUrlResolver->getWebsiteUrl($website));
    }

    public function testGetWebsiteSecureUrlHasSecureUrl(): void
    {
        $url = 'https://website.url/';
        $urlConfig = ['value' => $url];

        $website = $this->getWebsite(2);

        $this->memoryCacheProvider->expects(self::once())
            ->method('get')
            ->willReturnCallback(function ($arguments, $callable) {
                return $callable($arguments);
            });

        $this->configManager->expects(self::once())
            ->method('get')
            ->with(self::CONFIG_SECURE_URL, false, true, $website)
            ->willReturn($urlConfig);

        self::assertSame($url, $this->websiteUrlResolver->getWebsiteSecureUrl($website));
    }

    public function testGetWebsiteSecureUrlHasSecureUrlWhenDataCached(): void
    {
        $url = 'https://website.url/';

        $website = $this->getWebsite(2);

        $this->configManager->expects(self::never())
            ->method('get');

        $this->memoryCacheProvider->expects(self::once())
            ->method('get')
            ->willReturnCallback(function () use ($url) {
                return $url;
            });

        self::assertSame($url, $this->websiteUrlResolver->getWebsiteSecureUrl($website));
    }

    public function testGetWebsiteSecureUrlHasUrl(): void
    {
        $secureUrl = 'http://global.website.url/';
        $url = 'https://website.url/';
        $secureUrlConfig = ['value' => $secureUrl, 'use_parent_scope_value' => true];
        $urlConfig = ['value' => $url];

        $website = $this->getWebsite(2);

        $this->memoryCacheProvider->expects(self::once())
            ->method('get')
            ->willReturnCallback(function ($arguments, $callable) {
                return $callable($arguments);
            });

        $this->configManager->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive(
                [self::CONFIG_SECURE_URL, false, true, $website],
                [self::CONFIG_URL, false, true, $website]
            )
            ->willReturnMap([
                [self::CONFIG_SECURE_URL, false, true, $website, $secureUrlConfig],
                [self::CONFIG_URL, false, true, $website, $urlConfig]
            ]);

        self::assertSame($url, $this->websiteUrlResolver->getWebsiteSecureUrl($website));
    }

    public function testGetWebsiteSecureUrlHasGlobalSecureUrl(): void
    {
        $secureUrl = 'http://global.website.url/';
        $url = 'https://global.website.url/';
        $secureUrlConfig = ['value' => $secureUrl, 'use_parent_scope_value' => true];
        $urlConfig = ['value' => $url, 'use_parent_scope_value' => true];

        $website = $this->getWebsite(2);

        $this->memoryCacheProvider->expects(self::once())
            ->method('get')
            ->willReturnCallback(function ($arguments, $callable) {
                return $callable($arguments);
            });

        $this->configManager->expects(self::exactly(3))
            ->method('get')
            ->withConsecutive(
                [self::CONFIG_SECURE_URL, false, true, $website],
                [self::CONFIG_URL, false, true, $website],
                [self::CONFIG_SECURE_URL, true, false, $website]
            )
            ->willReturnMap([
                [self::CONFIG_SECURE_URL, false, true, $website, $secureUrlConfig],
                [self::CONFIG_URL, false, true, $website, $urlConfig],
                [self::CONFIG_SECURE_URL, true, false, $website, $secureUrl]
            ]);

        self::assertSame($secureUrl, $this->websiteUrlResolver->getWebsiteSecureUrl($website));
    }

    public function testGetWebsiteSecureUrlHasGlobalUrl(): void
    {
        $url = 'https://global.website.url/';
        $secureUrlConfig = ['value' => null, 'use_parent_scope_value' => true];
        $urlConfig = ['value' => $url, 'use_parent_scope_value' => true];

        $website = $this->getWebsite(2);

        $this->memoryCacheProvider->expects(self::once())
            ->method('get')
            ->willReturnCallback(function ($arguments, $callable) {
                return $callable($arguments);
            });

        $this->configManager->expects(self::exactly(4))
            ->method('get')
            ->withConsecutive(
                [self::CONFIG_SECURE_URL, false, true, $website],
                [self::CONFIG_URL, false, true, $website],
                [self::CONFIG_SECURE_URL, true, false, $website],
                [self::CONFIG_URL, true, false, $website]
            )
            ->willReturnMap([
                [self::CONFIG_SECURE_URL, false, true, $website, $secureUrlConfig],
                [self::CONFIG_URL, false, true, $website, $urlConfig],
                [self::CONFIG_SECURE_URL, true, false, $website, null],
                [self::CONFIG_URL, true, false, $website, $url]
            ]);

        self::assertSame($url, $this->websiteUrlResolver->getWebsiteSecureUrl($website));
    }

    public function testGetWebsitePath(): void
    {
        $route = 'test';
        $routeParams = ['id' => 1];
        $url = 'http://global.website.url/';

        $website = $this->getWebsite(2);

        $this->memoryCacheProvider->expects(self::once())
            ->method('get')
            ->willReturnCallback(function ($arguments, $callable) {
                return $callable($arguments);
            });

        $this->configManager->expects(self::once())
            ->method('get')
            ->with(self::CONFIG_URL, false, false, $website)
            ->willReturn($url);

        $this->urlGenerator->expects(self::once())
            ->method('generate')
            ->with($route, $routeParams)
            ->willReturn('/test/1');

        self::assertSame(
            'http://global.website.url/test/1',
            $this->websiteUrlResolver->getWebsitePath($route, $routeParams, $website)
        );
    }

    public function testGetWebsitePathWhenDataCached(): void
    {
        $route = 'test';
        $routeParams = ['id' => 1];
        $path = 'http://global.website.url/test/1';

        $website = $this->getWebsite(2);

        $this->memoryCacheProvider->expects(self::once())
            ->method('get')
            ->willReturnCallback(function () use ($path) {
                return $path;
            });

        $this->configManager->expects(self::never())
            ->method('get');

        $this->urlGenerator->expects(self::once())
            ->method('generate')
            ->with($route, $routeParams)
            ->willReturn('/test/1');

        self::assertSame(
            $path,
            $this->websiteUrlResolver->getWebsitePath($route, $routeParams, $website)
        );
    }

    public function testGetWebsitePathWithSubFolderPath(): void
    {
        $route = 'test';
        $routeParams = ['id' => 1];
        $url = 'http://global.website.url/some/subfolder/';

        $website = $this->getWebsite(2);

        $this->memoryCacheProvider->expects(self::once())
            ->method('get')
            ->willReturnCallback(function ($arguments, $callable) {
                return $callable($arguments);
            });

        $this->configManager->expects(self::once())
            ->method('get')
            ->with(self::CONFIG_URL, false, false, $website)
            ->willReturn($url);

        $this->urlGenerator->expects(self::once())
            ->method('generate')
            ->with($route, $routeParams)
            ->willReturn('/some/subfolder/test/1');

        self::assertSame(
            'http://global.website.url/some/subfolder/test/1',
            $this->websiteUrlResolver->getWebsitePath($route, $routeParams, $website)
        );
    }

    public function testGetWebsiteSecurePath(): void
    {
        $route = 'test';
        $routeParams = ['id' => 1];
        $url = 'https://website.url/';
        $urlConfig = ['value' => $url];

        $website = $this->getWebsite(2);

        $this->memoryCacheProvider->expects(self::once())
            ->method('get')
            ->willReturnCallback(function ($arguments, $callable) {
                return $callable($arguments);
            });

        $this->configManager->expects(self::once())
            ->method('get')
            ->with(self::CONFIG_SECURE_URL, false, true, $website)
            ->willReturn($urlConfig);

        $this->urlGenerator->expects(self::once())
            ->method('generate')
            ->with($route, $routeParams)
            ->willReturn('/test/1');

        self::assertSame(
            'https://website.url/test/1',
            $this->websiteUrlResolver->getWebsiteSecurePath($route, $routeParams, $website)
        );
    }

    public function testGetWebsiteSecurePathWhenDataCached(): void
    {
        $route = 'test';
        $routeParams = ['id' => 1];
        $path = 'https://website.url/test/1';

        $website = $this->getWebsite(2);

        $this->memoryCacheProvider->expects(self::once())
            ->method('get')
            ->willReturnCallback(function () use ($path) {
                return $path;
            });

        $this->configManager->expects(self::never())
            ->method('get');

        $this->urlGenerator->expects(self::once())
            ->method('generate')
            ->with($route, $routeParams)
            ->willReturn('/test/1');

        self::assertSame(
            $path,
            $this->websiteUrlResolver->getWebsiteSecurePath($route, $routeParams, $website)
        );
    }

    public function testGetWebsiteSecurePathWithSubFolderPathAndPort(): void
    {
        $route = 'test';
        $routeParams = ['id' => 1];
        $url = 'https://website.url:8080/some/subfolder/';
        $urlConfig = ['value' => $url];

        $website = $this->getWebsite(2);

        $this->memoryCacheProvider->expects(self::once())
            ->method('get')
            ->willReturnCallback(function ($arguments, $callable) {
                return $callable($arguments);
            });

        $this->configManager->expects(self::once())
            ->method('get')
            ->with(self::CONFIG_SECURE_URL, false, true, $website)
            ->willReturn($urlConfig);

        $this->urlGenerator->expects(self::once())
            ->method('generate')
            ->with($route, $routeParams)
            ->willReturn('/some/subfolder/test/1');

        self::assertSame(
            'https://website.url:8080/some/subfolder/test/1',
            $this->websiteUrlResolver->getWebsiteSecurePath($route, $routeParams, $website)
        );
    }
}
