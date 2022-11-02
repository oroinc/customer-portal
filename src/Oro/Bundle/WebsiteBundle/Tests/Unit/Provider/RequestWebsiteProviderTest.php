<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Unit\Provider;

use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Bundle\WebsiteBundle\Provider\RequestWebsiteProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class RequestWebsiteProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var RequestStack|\PHPUnit\Framework\MockObject\MockObject */
    private $requestStack;

    /** @var WebsiteManager|\PHPUnit\Framework\MockObject\MockObject */
    private $websiteManager;

    /** @var RequestWebsiteProvider */
    private $provider;

    protected function setUp(): void
    {
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->websiteManager = $this->createMock(WebsiteManager::class);

        $this->provider = new RequestWebsiteProvider(
            $this->requestStack,
            $this->websiteManager
        );
    }

    public function testGetWebsiteWhenNoRequest(): void
    {
        $this->requestStack->expects($this->once())
            ->method('getMainRequest')
            ->willReturn(null);
        $this->websiteManager->expects($this->never())
            ->method('getCurrentWebsite');

        self::assertNull($this->provider->getWebsite());
    }

    public function testGetWebsiteWhenHasRequestAttribute(): void
    {
        $request = Request::create('/');
        $website = $this->createMock(Website::class);

        $request->attributes->set('current_website', $website);

        $this->requestStack->expects($this->once())
            ->method('getMainRequest')
            ->willReturn($request);
        $this->websiteManager->expects($this->never())
            ->method('getCurrentWebsite');

        self::assertSame($website, $this->provider->getWebsite());
    }

    public function testGetWebsiteWhenNoRequestAttribute(): void
    {
        $request = Request::create('/');
        $website = $this->createMock(Website::class);

        $this->requestStack->expects($this->once())
            ->method('getMainRequest')
            ->willReturn($request);
        $this->websiteManager->expects($this->once())
            ->method('getCurrentWebsite')
            ->willReturn($website);

        self::assertSame($website, $this->provider->getWebsite());
        self::assertTrue($request->attributes->has('current_website'));
        self::assertSame($website, $request->attributes->get('current_website'));
    }

    public function testGetWebsiteWhenNoRequestAttributeAndNoCurrentWebsite(): void
    {
        $request = Request::create('/');

        $this->requestStack->expects($this->once())
            ->method('getMainRequest')
            ->willReturn($request);
        $this->websiteManager->expects($this->once())
            ->method('getCurrentWebsite')
            ->willReturn(null);

        self::assertNull($this->provider->getWebsite());
        self::assertTrue($request->attributes->has('current_website'));
        self::assertNull($request->attributes->get('current_website'));
    }
}
