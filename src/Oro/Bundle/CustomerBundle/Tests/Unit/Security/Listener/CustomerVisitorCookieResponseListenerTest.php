<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Security\Listener;

use Oro\Bundle\CustomerBundle\Security\Firewall\AnonymousCustomerUserAuthenticationListener;
use Oro\Bundle\CustomerBundle\Security\Listener\CustomerVisitorCookieResponseListener;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class CustomerVisitorCookieResponseListenerTest extends \PHPUnit\Framework\TestCase
{
    public function testOnKernelResponse()
    {
        $cookie = new Cookie('foo_cookie');

        $request = new Request();
        $request->attributes->set(AnonymousCustomerUserAuthenticationListener::COOKIE_ATTR_NAME, $cookie);

        $response = new Response();

        $listener = new CustomerVisitorCookieResponseListener();
        $event = $this->createMock(FilterResponseEvent::class);
        $event->expects($this->once())
            ->method('isMasterRequest')
            ->willReturn(true);

        $event->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);

        $event->expects($this->once())
            ->method('getResponse')
            ->willReturn($response);

        $listener->onKernelResponse($event);

        $this->assertEquals([$cookie], $response->headers->getCookies());
    }

    public function testOnKernelResponseWithoutCookieInAttribute()
    {
        $request = new Request();

        $response = new Response();

        $listener = new CustomerVisitorCookieResponseListener();
        $event = $this->createMock(FilterResponseEvent::class);
        $event->expects($this->once())
            ->method('isMasterRequest')
            ->willReturn(true);

        $event->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);

        $event->expects($this->once())
            ->method('getResponse')
            ->willReturn($response);

        $listener->onKernelResponse($event);

        $this->assertEmpty($response->headers->getCookies());
    }

    public function testOnKernelResponseNotMasterRequest()
    {
        $listener = new CustomerVisitorCookieResponseListener();
        $event = $this->createMock(FilterResponseEvent::class);
        $event->expects($this->once())
            ->method('isMasterRequest')
            ->willReturn(false);

        $event->expects($this->never())
            ->method('getRequest');

        $listener->onKernelResponse($event);
    }
}
