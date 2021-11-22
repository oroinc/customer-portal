<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Unit\Resolver;

use Oro\Bundle\CacheBundle\Tests\Unit\Provider\MemoryCacheProviderAwareTestTrait;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Resolver\WebsiteUrlResolver;
use Oro\Component\Testing\Unit\EntityTrait;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class WebsiteUrlResolverTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;
    use MemoryCacheProviderAwareTestTrait;

    private const CONFIG_URL = 'oro_website.url';
    private const CONFIG_SECURE_URL = 'oro_website.secure_url';

    /** @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject */
    private $configManager;

    /** @var UrlGeneratorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $urlGenerator;

    /** @var WebsiteUrlResolver */
    private $websiteUrlResolver;

    protected function setUp(): void
    {
        $this->configManager = $this->createMock(ConfigManager::class);
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);

        $this->websiteUrlResolver = new WebsiteUrlResolver($this->configManager, $this->urlGenerator);
    }

    public function testGetWebsiteUrl()
    {
        $url = 'http://global.website.url/';

        /** @var Website $website */
        $website = $this->getEntity(Website::class, ['id' => 2]);
        $this->configManager->expects($this->once())
            ->method('get')
            ->with(self::CONFIG_URL, false, false, $website)
            ->willReturn($url);

        $this->assertSame($url, $this->websiteUrlResolver->getWebsiteUrl($website));
    }

    public function testGetWebsiteUrlWhenMemoryCacheProvider(): void
    {
        $this->mockMemoryCacheProvider();
        $this->setMemoryCacheProvider($this->websiteUrlResolver);

        $this->testGetWebsiteUrl();
    }

    public function testGetWebsiteUrlWhenCache(): void
    {
        $url = 'http://global.website.url/';

        /** @var Website $website */
        $website = $this->getEntity(Website::class, ['id' => 2]);

        $this->configManager->expects($this->never())
            ->method('get');

        $this->mockMemoryCacheProvider($url);
        $this->setMemoryCacheProvider($this->websiteUrlResolver);
        $this->assertSame($url, $this->websiteUrlResolver->getWebsiteUrl($website));
    }

    public function testGetWebsiteSecureUrlHasSecureUrl()
    {
        $url = 'https://website.url/';
        $urlConfig = [
            'value' => $url
        ];

        /** @var Website $website */
        $website = $this->getEntity(Website::class, ['id' => 2]);

        $this->configManager->expects($this->once())
            ->method('get')
            ->with(self::CONFIG_SECURE_URL, false, true, $website)
            ->willReturn($urlConfig);

        $this->assertSame($url, $this->websiteUrlResolver->getWebsiteSecureUrl($website));
    }

    public function testGetWebsiteSecureUrlHasSecureUrlWhenMemoryCacheProvider(): void
    {
        $this->mockMemoryCacheProvider();
        $this->setMemoryCacheProvider($this->websiteUrlResolver);

        $this->testGetWebsiteSecureUrlHasSecureUrl();
    }

    public function testGetWebsiteSecureUrlHasSecureUrlWhenCache(): void
    {
        $url = 'https://website.url/';

        /** @var Website $website */
        $website = $this->getEntity(Website::class, ['id' => 2]);

        $this->configManager->expects($this->never())
            ->method('get');

        $this->mockMemoryCacheProvider($url);
        $this->setMemoryCacheProvider($this->websiteUrlResolver);

        $this->assertSame($url, $this->websiteUrlResolver->getWebsiteSecureUrl($website));
    }

    public function testGetWebsiteSecureUrlHasUrl()
    {
        $secureUrl = 'http://global.website.url/';
        $url = 'https://website.url/';
        $secureUrlConfig = [
            'value' => $secureUrl,
            'use_parent_scope_value' => true
        ];
        $urlConfig = [
            'value' => $url
        ];

        /** @var Website $website */
        $website = $this->getEntity(Website::class, ['id' => 2]);

        $this->configManager->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(
                [self::CONFIG_SECURE_URL, false, true, $website],
                [self::CONFIG_URL, false, true, $website]
            )
            ->willReturnMap([
                [self::CONFIG_SECURE_URL, false, true, $website, $secureUrlConfig],
                [self::CONFIG_URL, false, true, $website, $urlConfig]
            ]);

        $this->assertSame($url, $this->websiteUrlResolver->getWebsiteSecureUrl($website));
    }

    public function testGetWebsiteSecureUrlHasGlobalSecureUrl()
    {
        $secureUrl = 'http://global.website.url/';
        $url = 'https://global.website.url/';
        $secureUrlConfig = [
            'value' => $secureUrl,
            'use_parent_scope_value' => true
        ];
        $urlConfig = [
            'value' => $url,
            'use_parent_scope_value' => true
        ];

        /** @var Website $website */
        $website = $this->getEntity(Website::class, ['id' => 2]);

        $this->configManager->expects($this->exactly(3))
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

        $this->assertSame($secureUrl, $this->websiteUrlResolver->getWebsiteSecureUrl($website));
    }

    public function testGetWebsiteSecureUrlHasGlobalUrl()
    {
        $url = 'https://global.website.url/';
        $secureUrlConfig = [
            'value' => null,
            'use_parent_scope_value' => true
        ];
        $urlConfig = [
            'value' => $url,
            'use_parent_scope_value' => true
        ];

        /** @var Website $website */
        $website = $this->getEntity(Website::class, ['id' => 2]);

        $this->configManager->expects($this->exactly(4))
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

        $this->assertSame($url, $this->websiteUrlResolver->getWebsiteSecureUrl($website));
    }

    public function testGetWebsitePath()
    {
        $route = 'test';
        $routeParams = ['id' =>1 ];
        $url = 'http://global.website.url/';

        /** @var Website $website */
        $website = $this->getEntity(Website::class, ['id' => 2]);
        $this->configManager->expects($this->once())
            ->method('get')
            ->with(self::CONFIG_URL, false, false, $website)
            ->willReturn($url);
        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with($route, $routeParams)
            ->willReturn('/test/1');

        $this->assertSame(
            'http://global.website.url/test/1',
            $this->websiteUrlResolver->getWebsitePath($route, $routeParams, $website)
        );
    }

    public function testGetWebsitePathWithSubfolderPath()
    {
        $route = 'test';
        $routeParams = ['id' =>1 ];
        $url = 'http://global.website.url/some/subfolder/';

        /** @var Website $website */
        $website = $this->getEntity(Website::class, ['id' => 2]);
        $this->configManager->expects($this->once())
            ->method('get')
            ->with(self::CONFIG_URL, false, false, $website)
            ->willReturn($url);
        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with($route, $routeParams)
            ->willReturn('/some/subfolder/test/1');

        $this->assertSame(
            'http://global.website.url/some/subfolder/test/1',
            $this->websiteUrlResolver->getWebsitePath($route, $routeParams, $website)
        );
    }

    public function testGetWebsiteSecurePath()
    {
        $route = 'test';
        $routeParams = ['id' =>1 ];
        $url = 'https://website.url/';
        $urlConfig = [
            'value' => $url
        ];

        /** @var Website $website */
        $website = $this->getEntity(Website::class, ['id' => 2]);
        $this->configManager->expects($this->once())
            ->method('get')
            ->with(self::CONFIG_SECURE_URL, false, true, $website)
            ->willReturn($urlConfig);
        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with($route, $routeParams)
            ->willReturn('/test/1');

        $this->assertSame(
            'https://website.url/test/1',
            $this->websiteUrlResolver->getWebsiteSecurePath($route, $routeParams, $website)
        );
    }

    public function testGetWebsiteSecurePathWithSubfolderPathAndPort()
    {
        $route = 'test';
        $routeParams = ['id' =>1 ];
        $url = 'https://website.url:8080/some/subfolder/';
        $urlConfig = [
            'value' => $url
        ];

        /** @var Website $website */
        $website = $this->getEntity(Website::class, ['id' => 2]);
        $this->configManager->expects($this->once())
            ->method('get')
            ->with(self::CONFIG_SECURE_URL, false, true, $website)
            ->willReturn($urlConfig);
        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with($route, $routeParams)
            ->willReturn('/some/subfolder/test/1');

        $this->assertSame(
            'https://website.url:8080/some/subfolder/test/1',
            $this->websiteUrlResolver->getWebsiteSecurePath($route, $routeParams, $website)
        );
    }
}
