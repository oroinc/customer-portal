<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Security\Listener;

use Oro\Bundle\CustomerBundle\Security\Firewall\AnonymousCustomerUserAuthenticationListener;
use Oro\Bundle\CustomerBundle\Security\Listener\CustomerVisitorCookieResponseListener;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class CustomerVisitorCookieResponseListenerTest extends \PHPUnit\Framework\TestCase
{
    public function testOnKernelResponse(): void
    {
        $cookie = Cookie::create('foo_cookie');

        $request = new Request();
        $request->attributes->set(AnonymousCustomerUserAuthenticationListener::COOKIE_ATTR_NAME, $cookie);

        $response = new Response();

        $listener = new CustomerVisitorCookieResponseListener();
        $event = $this->createMock(ResponseEvent::class);
        $event->expects(self::once())
            ->method('isMasterRequest')
            ->willReturn(true);

        $event->expects(self::once())
            ->method('getRequest')
            ->willReturn($request);

        $event->expects(self::once())
            ->method('getResponse')
            ->willReturn($response);

        $listener->onKernelResponse($event);

        self::assertEquals([$cookie], $response->headers->getCookies());
    }

    public function testOnKernelResponseWithoutCookieInAttribute(): void
    {
        $request = new Request();

        $response = new Response();

        $listener = new CustomerVisitorCookieResponseListener();
        $event = $this->createMock(ResponseEvent::class);
        $event->expects(self::once())
            ->method('isMasterRequest')
            ->willReturn(true);

        $event->expects(self::once())
            ->method('getRequest')
            ->willReturn($request);

        $event->expects(self::once())
            ->method('getResponse')
            ->willReturn($response);

        $listener->onKernelResponse($event);

        self::assertEmpty($response->headers->getCookies());
    }

    public function testOnKernelResponseNotMasterRequest(): void
    {
        $listener = new CustomerVisitorCookieResponseListener();
        $event = $this->createMock(ResponseEvent::class);
        $event->expects(self::once())
            ->method('isMasterRequest')
            ->willReturn(false);

        $event->expects(self::never())
            ->method('getRequest');

        $listener->onKernelResponse($event);
    }
}
