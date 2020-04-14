<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Unit\EventListener;

use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\EventListener\RedirectListener;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Bundle\WebsiteBundle\Resolver\WebsiteUrlResolver;
use Oro\Component\Testing\Unit\EntityTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class RedirectListenerTest extends TestCase
{
    use EntityTrait;

    /** @var RedirectListener */
    private $listener;

    /** @var WebsiteManager|MockObject */
    private $websiteManager;

    /** @var WebsiteUrlResolver|MockObject */
    private $urlResolver;

    /** @var FrontendHelper */
    private $frontendHelper;

    /**
     * {@inheritdoc}
     */
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

    public function testNoRedirectWhenSubrequest()
    {
        $this->frontendHelper->method('isFrontendRequest')->willReturn(true);
        /** @var GetResponseEvent|MockObject $event */
        $event = $this->createMock(GetResponseEvent::class);
        $event->method('isMasterRequest')->willReturn(false);

        // assert redirect response not set
        $event->expects($this->never())
            ->method('setResponse');
        $this->listener->onRequest($event);
    }

    public function testNoRedirectWhenUrlMatchWithBase()
    {
        $this->frontendHelper->method('isFrontendRequest')->willReturn(true);
        /** @var Website $website */
        $website = $this->getEntity(Website::class, ['id' => 1]);

        $request = Request::create('https://eu.orocommerce.com/product?test=1&test2=2');

        /** @var GetResponseEvent|MockObject $event */
        $event = $this->createMock(GetResponseEvent::class);

        $event->expects($this->once())->method('getRequest')->willReturn($request);
        $event->expects($this->once())->method('isMasterRequest')->willReturn(true);

        $this->websiteManager->expects($this->once())
            ->method('getCurrentWebsite')
            ->willReturn($website);

        $this->urlResolver->expects($this->once())
            ->method('getWebsiteUrl')
            ->willReturnMap([
                [$website, true, 'https://eu.orocommerce.com']
            ]);

        $event->expects($this->never())
            ->method('setResponse')
            ->willReturnCallback(
                function (RedirectResponse $response) {
                    $this->assertEquals(
                        'https://eu.orocommerce.com/product?test=1&test2=2',
                        $response->getTargetUrl()
                    );
                }
            );

        $this->listener->onRequest($event);
    }

    public function testRedirectToBaseUrl()
    {
        $this->frontendHelper->method('isFrontendRequest')->willReturn(true);
        /** @var Website $website */
        $website = $this->getEntity(Website::class, ['id' => 1]);

        $request = Request::create('https://ua.orocommerce.com/product?a=b');

        /** @var ParameterBag|MockObject $parameterBag */
        $parameterBag = $this->createMock(ParameterBag::class);
        $parameterBag->expects($this->once())
            ->method('all')
            ->willReturn(['a' => 'b']);
        $request->query = $parameterBag;

        /** @var GetResponseEvent|MockObject $event */
        $event = $this->createMock(GetResponseEvent::class);
        $event->expects($this->once())->method('getRequest')->willReturn($request);
        $event->expects($this->once())->method('isMasterRequest')->willReturn(true);

        $this->websiteManager->expects($this->once())
            ->method('getCurrentWebsite')
            ->willReturn($website);

        $this->urlResolver->expects($this->once())
            ->method('getWebsiteUrl')
            ->willReturnMap([
                [$website, true, 'https://eu.orocommerce.com']
            ]);

        $event->expects($this->once())
            ->method('setResponse')
            ->willReturnCallback(
                function (RedirectResponse $response) {
                    $this->assertEquals(
                        'https://eu.orocommerce.com/product?a=b',
                        $response->getTargetUrl()
                    );
                }
            );

        $this->listener->onRequest($event);
    }
}
