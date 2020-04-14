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

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->websiteManager = $this->createMock(WebsiteManager::class);

        $this->provider = new RequestWebsiteProvider(
            $this->requestStack,
            $this->websiteManager
        );
    }

    public function testGetWebsiteWhenNoRequest()
    {
        $this->requestStack->expects($this->once())
            ->method('getMasterRequest')
            ->willReturn(null);
        $this->websiteManager->expects($this->never())
            ->method('getCurrentWebsite');

        $this->assertNull($this->provider->getWebsite());
    }

    public function testGetWebsiteWhenHasRequestAttribute()
    {
        $request = Request::create('/');
        $website = $this->createMock(Website::class);

        $request->attributes->set('current_website', $website);

        $this->requestStack->expects($this->once())
            ->method('getMasterRequest')
            ->willReturn($request);
        $this->websiteManager->expects($this->never())
            ->method('getCurrentWebsite');

        $this->assertSame($website, $this->provider->getWebsite());
    }

    public function testGetWebsiteWhenNoRequestAttribute()
    {
        $request = Request::create('/');
        $website = $this->createMock(Website::class);

        $this->requestStack->expects($this->once())
            ->method('getMasterRequest')
            ->willReturn($request);
        $this->websiteManager->expects($this->once())
            ->method('getCurrentWebsite')
            ->willReturn($website);

        $this->assertSame($website, $this->provider->getWebsite());
        $this->assertTrue($request->attributes->has('current_website'));
        $this->assertSame($website, $request->attributes->get('current_website'));
    }

    public function testGetWebsiteWhenNoRequestAttributeAndNoCurrentWebsite()
    {
        $request = Request::create('/');

        $this->requestStack->expects($this->once())
            ->method('getMasterRequest')
            ->willReturn($request);
        $this->websiteManager->expects($this->once())
            ->method('getCurrentWebsite')
            ->willReturn(null);

        $this->assertNull($this->provider->getWebsite());
        $this->assertTrue($request->attributes->has('current_website'));
        $this->assertNull($request->attributes->get('current_website'));
    }
}
