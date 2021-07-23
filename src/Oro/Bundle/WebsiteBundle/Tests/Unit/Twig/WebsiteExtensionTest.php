<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Unit\Twig;

use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Bundle\WebsiteBundle\Resolver\WebsiteUrlResolver;
use Oro\Bundle\WebsiteBundle\Twig\WebsiteExtension;
use Oro\Component\Testing\Unit\TwigExtensionTestCaseTrait;

class WebsiteExtensionTest extends \PHPUnit\Framework\TestCase
{
    use TwigExtensionTestCaseTrait;

    /** @var WebsiteManager|\PHPUnit\Framework\MockObject\MockObject */
    private $websiteManager;

    /** @var WebsiteUrlResolver|\PHPUnit\Framework\MockObject\MockObject */
    private $websiteUrlResolver;

    /** @var WebsiteExtension */
    private $extension;

    protected function setUp(): void
    {
        $this->websiteManager = $this->createMock(WebsiteManager::class);
        $this->websiteUrlResolver = $this->createMock(WebsiteUrlResolver::class);

        $container = self::getContainerBuilder()
            ->add('oro_website.manager', $this->websiteManager)
            ->add('oro_website.resolver.website_url_resolver', $this->websiteUrlResolver)
            ->getContainer($this);

        $this->extension = new WebsiteExtension($container);
    }

    /**
     * @dataProvider getCurrentWebsiteDataProvider
     */
    public function testGetCurrentWebsite(?Website $website)
    {
        $this->websiteManager->expects(self::once())
            ->method('getCurrentWebsite')
            ->willReturn($website);

        $this->assertSame(
            $website,
            self::callTwigFunction($this->extension, 'oro_website_get_current_website', [])
        );
    }

    public function getCurrentWebsiteDataProvider(): array
    {
        return [
            [new Website()],
            [null]
        ];
    }

    public function testGetWebsitePath()
    {
        $route = 'test_route';
        $routeParams = ['key' => 'value'];
        $website = new Website();
        $websitePath = 'test_path';

        $this->websiteUrlResolver->expects(self::once())
            ->method('getWebsitePath')
            ->with($route, $routeParams, $website)
            ->willReturn($websitePath);

        $this->assertEquals(
            $websitePath,
            self::callTwigFunction($this->extension, 'website_path', [$route, $routeParams, $website])
        );
    }

    public function testGetWebsitePathWhenNoWebsite()
    {
        $route = 'test_route';
        $routeParams = ['key' => 'value'];
        $websitePath = 'test_path';

        $this->websiteUrlResolver->expects(self::once())
            ->method('getWebsitePath')
            ->with($route, $routeParams, self::isNull())
            ->willReturn($websitePath);

        $this->assertEquals(
            $websitePath,
            self::callTwigFunction($this->extension, 'website_path', [$route, $routeParams])
        );
    }

    public function testGetWebsitePathWhenNoRouteParams()
    {
        $route = 'test_route';
        $websitePath = 'test_path';

        $this->websiteUrlResolver->expects(self::once())
            ->method('getWebsitePath')
            ->with($route, self::identicalTo([]), self::isNull())
            ->willReturn($websitePath);

        $this->assertEquals(
            $websitePath,
            self::callTwigFunction($this->extension, 'website_path', [$route])
        );
    }

    public function testGetWebsiteSecurePath()
    {
        $route = 'test_route';
        $routeParams = ['key' => 'value'];
        $website = new Website();
        $websiteSecurePath = 'test_path';

        $this->websiteUrlResolver->expects(self::once())
            ->method('getWebsiteSecurePath')
            ->with($route, $routeParams, $website)
            ->willReturn($websiteSecurePath);

        $this->assertEquals(
            $websiteSecurePath,
            self::callTwigFunction($this->extension, 'website_secure_path', [$route, $routeParams, $website])
        );
    }

    public function testGetWebsiteSecurePathWhenNoWebsite()
    {
        $route = 'test_route';
        $routeParams = ['key' => 'value'];
        $websiteSecurePath = 'test_path';

        $this->websiteUrlResolver->expects(self::once())
            ->method('getWebsiteSecurePath')
            ->with($route, $routeParams, self::isNull())
            ->willReturn($websiteSecurePath);

        $this->assertEquals(
            $websiteSecurePath,
            self::callTwigFunction($this->extension, 'website_secure_path', [$route, $routeParams])
        );
    }

    public function testGetWebsiteSecurePathWhenNoRouteParams()
    {
        $route = 'test_route';
        $websiteSecurePath = 'test_path';

        $this->websiteUrlResolver->expects(self::once())
            ->method('getWebsiteSecurePath')
            ->with($route, self::identicalTo([]), self::isNull())
            ->willReturn($websiteSecurePath);

        $this->assertEquals(
            $websiteSecurePath,
            self::callTwigFunction($this->extension, 'website_secure_path', [$route])
        );
    }
}
