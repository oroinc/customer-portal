<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Security\Listener;

use Oro\Bundle\CustomerBundle\Security\AnonymousCustomerUserAuthenticator;
use Oro\Bundle\CustomerBundle\Security\Listener\CustomerVisitorCookieResponseListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class CustomerVisitorCookieResponseListenerTest extends TestCase
{
    private CustomerVisitorCookieResponseListener $listener;

    #[\Override]
    protected function setUp(): void
    {
        $this->listener = new CustomerVisitorCookieResponseListener();
    }

    private function getEvent(
        Request $request,
        Response $response,
        int $requestType = HttpKernelInterface::MAIN_REQUEST
    ): ResponseEvent {
        return new ResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            $requestType,
            $response
        );
    }

    private function getCookie(string $sessionId, ?int $visitorId): Cookie
    {
        return Cookie::create(
            AnonymousCustomerUserAuthenticator::COOKIE_ATTR_NAME,
            base64_encode(json_encode([$visitorId, $sessionId], JSON_THROW_ON_ERROR))
        );
    }

    public function testOnKernelResponseForSubRequest(): void
    {
        $cookie = $this->getCookie('session_id', 1);

        $request = new Request();
        $request->attributes->set(AnonymousCustomerUserAuthenticator::COOKIE_ATTR_NAME, $cookie);
        $response = new Response();

        $this->listener->onKernelResponse($this->getEvent($request, $response, HttpKernelInterface::SUB_REQUEST));

        self::assertEmpty($response->headers->getCookies());
    }

    public function testOnKernelResponseWithoutCookieInAttribute(): void
    {
        $request = new Request();
        $response = new Response();

        $this->listener->onKernelResponse($this->getEvent($request, $response));

        self::assertEmpty($response->headers->getCookies());
    }

    public function testOnKernelResponseWithCookieInAttribute(): void
    {
        $cookie = $this->getCookie('session_id', 1);

        $request = new Request();
        $request->attributes->set(AnonymousCustomerUserAuthenticator::COOKIE_ATTR_NAME, $cookie);
        $response = new Response();

        $this->listener->onKernelResponse($this->getEvent($request, $response));

        self::assertSame([$cookie], $response->headers->getCookies());
    }
}
