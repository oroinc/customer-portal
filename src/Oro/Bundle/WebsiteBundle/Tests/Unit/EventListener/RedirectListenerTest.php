<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Unit\EventListener;

use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\EventListener\RedirectListener;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Bundle\WebsiteBundle\Resolver\WebsiteUrlResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class RedirectListenerTest extends TestCase
{
    private WebsiteManager&MockObject $websiteManager;
    private WebsiteUrlResolver&MockObject $urlResolver;
    private FrontendHelper&MockObject $frontendHelper;
    private RedirectListener $listener;

    #[\Override]
    protected function setUp(): void
    {
        $this->websiteManager = $this->createMock(WebsiteManager::class);
        $this->urlResolver = $this->createMock(WebsiteUrlResolver::class);
        $this->frontendHelper = $this->createMock(FrontendHelper::class);

        $this->listener = new RedirectListener(
            $this->websiteManager,
            $this->urlResolver,
            $this->frontendHelper
        );
    }

    private function getEvent(bool $isMainRequest, Request $request, ?Response $response = null): RequestEvent
    {
        $event = new RequestEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            $isMainRequest ? HttpKernelInterface::MAIN_REQUEST : HttpKernelInterface::SUB_REQUEST
        );
        if (null !== $response) {
            $event->setResponse($response);
        }

        return $event;
    }

    public function testNoRedirectWhenSubRequest(): void
    {
        $this->frontendHelper->expects(self::never())
            ->method('isFrontendRequest');

        $request = Request::create('https://eu.orocommerce.com/product');
        $event = $this->getEvent(false, $request);
        $this->listener->onRequest($event);

        self::assertNull($event->getResponse());
    }

    public function testNoRedirectWhenMediaCache(): void
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $request = Request::create('https://eu.orocommerce.com/product');
        $response = new Response();
        $event = $this->getEvent(true, $request, $response);
        $this->listener->onRequest($event);

        self::assertSame($response, $event->getResponse());
    }

    public function testNoRedirectWhenUrlMatchWithBase(): void
    {
        $website = $this->createMock(Website::class);

        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);
        $this->websiteManager->expects(self::once())
            ->method('getCurrentWebsite')
            ->willReturn($website);
        $this->urlResolver->expects(self::once())
            ->method('getWebsiteUrl')
            ->willReturnMap([
                [$website, true, 'https://eu.orocommerce.com']
            ]);

        $request = Request::create('https://eu.orocommerce.com/product?test=1&test2=2');
        $response = new Response();
        $event = $this->getEvent(true, $request, $response);
        $this->listener->onRequest($event);

        self::assertSame($response, $event->getResponse());
    }

    public function testRedirectToBaseUrl(): void
    {
        $website = $this->createMock(Website::class);

        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);
        $this->websiteManager->expects(self::once())
            ->method('getCurrentWebsite')
            ->willReturn($website);
        $this->urlResolver->expects(self::once())
            ->method('getWebsiteUrl')
            ->willReturnMap([
                [$website, true, 'https://eu.orocommerce.com']
            ]);

        $request = Request::create('https://ua.orocommerce.com/product?a=b');
        $response = new Response();
        $event = $this->getEvent(true, $request, $response);
        $this->listener->onRequest($event);

        /** @var RedirectResponse $response */
        $response = $event->getResponse();
        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertEquals(
            'https://eu.orocommerce.com/product?a=b',
            $response->getTargetUrl()
        );
    }

    public function testNoRedirectForMediaCacheRequest(): void
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);
        $this->websiteManager->expects(self::never())
            ->method('getCurrentWebsite');
        $this->urlResolver->expects(self::never())
            ->method('getWebsiteUrl');

        $request = Request::create('https://ua.orocommerce.com/media/cache/product');
        $response = new Response();
        $event = $this->getEvent(true, $request, $response);
        $this->listener->onRequest($event);

        self::assertSame($response, $event->getResponse());
    }
}
