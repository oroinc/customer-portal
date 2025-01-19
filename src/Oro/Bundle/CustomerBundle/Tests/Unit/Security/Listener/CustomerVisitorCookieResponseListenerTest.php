<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Security\Listener;

use Oro\Bundle\CustomerBundle\Security\AnonymousCustomerUserAuthenticator;
use Oro\Bundle\CustomerBundle\Security\Firewall\CustomerVisitorCookieFactory;
use Oro\Bundle\CustomerBundle\Security\Listener\CustomerVisitorCookieResponseListener;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CustomerVisitorCookieResponseListenerTest extends \PHPUnit\Framework\TestCase
{
    protected CustomerVisitorCookieResponseListener $listener;

    public function setUp(): void
    {
        $this->listener = new CustomerVisitorCookieResponseListener(
            $this->createMock(TokenStorageInterface::class),
            $this->createMock(CustomerVisitorCookieFactory::class),
        );
    }

    public function testOnKernelResponse(): void
    {
        $cookie = Cookie::create('foo_cookie');

        $request = new Request();
        $request->attributes->set(AnonymousCustomerUserAuthenticator::COOKIE_ATTR_NAME, $cookie);

        $response = new Response();

        $event = new ResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $response
        );

        $this->listener->onKernelResponse($event);

        self::assertEquals([$cookie], $response->headers->getCookies());
    }

    public function testOnKernelResponseWithoutCookieInAttribute(): void
    {
        $request = new Request();
        $response = new Response();

        $event = new ResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $response
        );

        $this->listener->onKernelResponse($event);

        self::assertEmpty($response->headers->getCookies());
    }

    public function testOnKernelResponseNotMainRequest(): void
    {
        $response = new Response();

        $event = new ResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            new Request(),
            HttpKernelInterface::SUB_REQUEST,
            $response
        );

        $this->listener->onKernelResponse($event);

        self::assertEmpty($response->headers->getCookies());
    }
}
