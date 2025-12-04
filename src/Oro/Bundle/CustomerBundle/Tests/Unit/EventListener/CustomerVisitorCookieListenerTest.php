<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\EventListener;

use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;
use Oro\Bundle\CustomerBundle\EventListener\CustomerVisitorCookieListener;
use Oro\Bundle\CustomerBundle\Security\Firewall\AnonymousCustomerUserAuthenticationListener;
use Oro\Bundle\CustomerBundle\Security\Firewall\CustomerVisitorCookieFactory;
use Oro\Component\Testing\Unit\EntityTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class CustomerVisitorCookieListenerTest extends TestCase
{
    use EntityTrait;

    private CustomerVisitorCookieFactory&MockObject $cookieFactory;
    private RequestStack&MockObject $requestStack;
    private CustomerVisitorCookieListener $listener;

    #[\Override]
    protected function setUp(): void
    {
        $this->cookieFactory = $this->createMock(CustomerVisitorCookieFactory::class);
        $this->requestStack = $this->createMock(RequestStack::class);

        $this->listener = new CustomerVisitorCookieListener(
            $this->cookieFactory,
            $this->requestStack
        );
    }

    public function testCreateCookieIfNeededWhenVisitorHasNoSessionId(): void
    {
        $visitor = new CustomerVisitor();
        // No session ID set

        $this->requestStack->expects(self::never())
            ->method('getCurrentRequest');

        $this->cookieFactory->expects(self::never())
            ->method('getCookie');

        $this->listener->createCookieIfNeeded($visitor);
    }

    public function testCreateCookieIfNeededWhenNoCurrentRequest(): void
    {
        $visitor = new CustomerVisitor();
        $visitor->setSessionId('test-session-id');

        $this->requestStack->expects(self::once())
            ->method('getCurrentRequest')
            ->willReturn(null);

        $this->cookieFactory->expects(self::never())
            ->method('getCookie');

        $this->listener->createCookieIfNeeded($visitor);
    }

    public function testCreateCookieIfNeededWhenCookieAlreadyExists(): void
    {
        $visitor = new CustomerVisitor();
        $visitor->setSessionId('test-session-id');

        $request = new Request();
        $request->cookies->set(AnonymousCustomerUserAuthenticationListener::COOKIE_NAME, 'existing-cookie-value');

        $this->requestStack->expects(self::once())
            ->method('getCurrentRequest')
            ->willReturn($request);

        $this->cookieFactory->expects(self::never())
            ->method('getCookie');

        $this->listener->createCookieIfNeeded($visitor);

        self::assertFalse($request->attributes->has(AnonymousCustomerUserAuthenticationListener::COOKIE_ATTR_NAME));
    }

    public function testCreateCookieIfNeededSuccess(): void
    {
        $sessionId = 'test-session-id';
        $visitor = $this->getEntity(CustomerVisitor::class, [
            'id' => 12,
            'sessionId' => $sessionId,
        ]);

        $request = new Request();
        // No existing cookie

        $cookie = $this->createMock(Cookie::class);

        $this->requestStack->expects(self::once())
            ->method('getCurrentRequest')
            ->willReturn($request);

        $this->cookieFactory->expects(self::once())
            ->method('getCookie')
            ->with(12, $sessionId)
            ->willReturn($cookie);

        $this->listener->createCookieIfNeeded($visitor);

        self::assertTrue($request->attributes->has(AnonymousCustomerUserAuthenticationListener::COOKIE_ATTR_NAME));
        self::assertSame(
            $cookie,
            $request->attributes->get(AnonymousCustomerUserAuthenticationListener::COOKIE_ATTR_NAME)
        );
    }

    public function testPostPersist(): void
    {
        $sessionId = 'test-session-id';
        $visitor = $this->getEntity(CustomerVisitor::class, [
            'id' => 12,
            'sessionId' => $sessionId,
        ]);

        $request = new Request();
        $cookie = $this->createMock(Cookie::class);

        $this->requestStack->expects(self::once())
            ->method('getCurrentRequest')
            ->willReturn($request);

        $this->cookieFactory->expects(self::once())
            ->method('getCookie')
            ->with(12, $sessionId)
            ->willReturn($cookie);

        $this->listener->postPersist($visitor);

        self::assertTrue($request->attributes->has(AnonymousCustomerUserAuthenticationListener::COOKIE_ATTR_NAME));
        self::assertSame(
            $cookie,
            $request->attributes->get(AnonymousCustomerUserAuthenticationListener::COOKIE_ATTR_NAME)
        );
    }

    public function testPostPersistWithVisitorWithoutSessionId(): void
    {
        $visitor = new CustomerVisitor();
        // No session ID set

        $this->requestStack->expects(self::never())
            ->method('getCurrentRequest');

        $this->cookieFactory->expects(self::never())
            ->method('getCookie');

        $this->listener->postPersist($visitor);
    }

    public function testPostPersistWhenNoCurrentRequest(): void
    {
        $visitor = new CustomerVisitor();
        $visitor->setSessionId('test-session-id');

        $this->requestStack->expects(self::once())
            ->method('getCurrentRequest')
            ->willReturn(null);

        $this->cookieFactory->expects(self::never())
            ->method('getCookie');

        $this->listener->postPersist($visitor);
    }
}
