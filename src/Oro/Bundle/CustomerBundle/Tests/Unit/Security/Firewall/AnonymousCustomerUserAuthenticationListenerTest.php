<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Security\Firewall;

use Oro\Bundle\ApiBundle\Request\ApiRequestHelper;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;
use Oro\Bundle\CustomerBundle\Security\AnonymousCustomerUserRolesProvider;
use Oro\Bundle\CustomerBundle\Security\Firewall\AnonymousCustomerUserAuthenticationListener;
use Oro\Bundle\CustomerBundle\Security\Firewall\CustomerVisitorCookieFactory;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\Request\CsrfProtectedRequestHelper;
use Oro\Component\Testing\Logger\BufferingLogger;
use Oro\Component\Testing\ReflectionUtil;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AnonymousCustomerUserAuthenticationListenerTest extends \PHPUnit\Framework\TestCase
{
    /** @var TokenStorageInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $tokenStorage;

    /** @var AuthenticationManagerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $authenticationManager;

    /** @var CsrfProtectedRequestHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $csrfProtectedRequestHelper;

    /** @var CustomerVisitorCookieFactory|\PHPUnit\Framework\MockObject\MockObject */
    private $cookieFactory;

    /** @var AnonymousCustomerUserRolesProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $rolesProvider;

    /** @var ApiRequestHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $apiRequestHelper;

    /** @var BufferingLogger */
    private $logger;

    /** @var AnonymousCustomerUserAuthenticationListener */
    private $listener;

    protected function setUp(): void
    {
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->authenticationManager = $this->createMock(AuthenticationManagerInterface::class);
        $this->csrfProtectedRequestHelper = $this->createMock(CsrfProtectedRequestHelper::class);
        $this->cookieFactory = $this->createMock(CustomerVisitorCookieFactory::class);
        $this->rolesProvider = $this->createMock(AnonymousCustomerUserRolesProvider::class);
        $this->apiRequestHelper = $this->createMock(ApiRequestHelper::class);
        $this->logger = new BufferingLogger();

        $this->listener = new AnonymousCustomerUserAuthenticationlistener(
            $this->tokenStorage,
            $this->authenticationManager,
            $this->csrfProtectedRequestHelper,
            $this->cookieFactory,
            $this->rolesProvider,
            $this->apiRequestHelper,
            $this->logger,
        );
    }

    private function getRequestEvent(Request $request): RequestEvent
    {
        return new RequestEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );
    }

    private function getCustomerVisitor(int $id, string $sessionId): CustomerVisitor
    {
        $visitor = new CustomerVisitor();
        ReflectionUtil::setId($visitor, $id);
        $visitor->setSessionId($sessionId);

        return $visitor;
    }

    private function getCustomerVisitorCookieValue(CustomerVisitor $visitor): string
    {
        return base64_encode(json_encode([$visitor->getId(), $visitor->getSessionId()], JSON_THROW_ON_ERROR));
    }

    /**
     * @dataProvider handleDataProvider
     */
    public function testHandleForAnonymousCustomerUserToken(?AnonymousCustomerUserToken $token): void
    {
        $visitor = $this->getCustomerVisitor(4, 'someSessionId');
        $createdVisitorCookie = new Cookie(AnonymousCustomerUserAuthenticationListener::COOKIE_NAME);

        $request = Request::create('http://test.com/test');
        $request->cookies->set(
            AnonymousCustomerUserAuthenticationListener::COOKIE_NAME,
            $this->getCustomerVisitorCookieValue($visitor)
        );

        $authenticatedToken = new AnonymousCustomerUserToken('Anonymous Customer User');
        $authenticatedToken->setVisitor($visitor);

        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn($token);

        if (null === $token) {
            $this->apiRequestHelper->expects(self::once())
                ->method('isApiRequest')
                ->with('/test')
                ->willReturn(false);
        } else {
            $this->apiRequestHelper->expects(self::never())
                ->method('isApiRequest');
        }

        $this->rolesProvider->expects(self::once())
            ->method('getRoles')
            ->willReturn(['TEST_ANONYMOUS_ROLE']);

        $this->authenticationManager->expects(self::once())
            ->method('authenticate')
            ->willReturnCallback(function (AnonymousCustomerUserToken $token) use ($authenticatedToken) {
                self::assertEquals('Anonymous Customer User', $token->getUser());
                self::assertEquals(['TEST_ANONYMOUS_ROLE'], $token->getRoleNames());

                return $authenticatedToken;
            });

        $this->tokenStorage->expects(self::once())
            ->method('setToken')
            ->with(self::identicalTo($authenticatedToken));
        $this->cookieFactory->expects(self::once())
            ->method('getCookie')
            ->with($visitor->getId(), $visitor->getSessionId())
            ->willReturn($createdVisitorCookie);

        ($this->listener)($this->getRequestEvent($request));
        self::assertSame(
            $createdVisitorCookie,
            $request->attributes->get(AnonymousCustomerUserAuthenticationListener::COOKIE_ATTR_NAME)
        );
        self::assertSame($authenticatedToken, ReflectionUtil::getPropertyValue($this->listener, 'rememberedToken'));

        self::assertEquals(
            [
                ['info', 'Populated the TokenStorage with an Anonymous Customer User Token.', []]
            ],
            $this->logger->cleanLogs()
        );
    }

    public function handleDataProvider(): array
    {
        return [
            'null token'                       => [
                'token' => null
            ],
            'AnonymousCustomerUserToken token' => [
                'token' => new AnonymousCustomerUserToken('User')
            ]
        ];
    }

    public function testHandleWithRememberedToken(): void
    {
        $rememberedToken = new AnonymousCustomerUserToken('User');
        ReflectionUtil::setPropertyValue($this->listener, 'rememberedToken', $rememberedToken);

        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn(null);

        $this->tokenStorage->expects(self::once())
            ->method('setToken')
            ->with($rememberedToken);

        ($this->listener)($this->getRequestEvent(new Request()));
        self::assertNull(ReflectionUtil::getPropertyValue($this->listener, 'rememberedToken'));

        self::assertEmpty($this->logger->cleanLogs());
    }

    public function testHandleWhenNoGuestRoles(): void
    {
        $this->rolesProvider->expects(self::once())
            ->method('getRoles')
            ->willReturn([]);

        $token = new AnonymousCustomerUserToken(
            'User',
            [new CustomerUserRole()],
            null,
            new Organization()
        );
        $createdToken = new AnonymousCustomerUserToken('Anonymous Customer User');
        $createdToken->setCredentials([
            'visitor_id' => null,
            'session_id' => null
        ]);
        $authenticatedToken = new AnonymousCustomerUserToken('Anonymous Customer User');

        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn($token);

        $request = new Request();

        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn(null);

        $this->authenticationManager->expects(self::once())
            ->method('authenticate')
            ->with($createdToken)
            ->willReturn($authenticatedToken);

        $this->tokenStorage->expects(self::once())
            ->method('setToken')
            ->with(self::identicalTo($authenticatedToken));

        ($this->listener)($this->getRequestEvent($request));
        self::assertNull($request->attributes->get(AnonymousCustomerUserAuthenticationListener::COOKIE_ATTR_NAME));

        self::assertEquals(
            [
                ['info', 'Populated the TokenStorage with an Anonymous Customer User Token.', []]
            ],
            $this->logger->cleanLogs()
        );
    }

    public function testHandleWithAuthenticationException(): void
    {
        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn(null);

        $exception = new AuthenticationException();
        $this->authenticationManager->expects(self::once())
            ->method('authenticate')
            ->willThrowException($exception);

        ($this->listener)($this->getRequestEvent(new Request()));

        self::assertEquals(
            [
                ['info', 'Customer User anonymous authentication failed.', ['exception' => $exception]]
            ],
            $this->logger->cleanLogs()
        );
    }

    public function testHandleWithUnsupportedToken(): void
    {
        $visitor = $this->getCustomerVisitor(4, 'someSessionId');

        $request = new Request();
        $request->cookies->set(
            AnonymousCustomerUserAuthenticationListener::COOKIE_NAME,
            $this->getCustomerVisitorCookieValue($visitor)
        );

        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn($this->createMock(TokenInterface::class));

        $this->authenticationManager->expects(self::never())
            ->method('authenticate');

        ($this->listener)($this->getRequestEvent($request));

        self::assertEmpty($this->logger->cleanLogs());
    }

    public function testHandleWithAlreadyAuthenticatedAnonymousToken(): void
    {
        $token = new AnonymousCustomerUserToken(
            'User',
            [new CustomerUserRole('anon')],
            new CustomerVisitor(),
            new Organization()
        );
        $request = new Request();

        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn($token);

        $this->tokenStorage->expects(self::never())
            ->method('setToken');

        ($this->listener)($this->getRequestEvent($request));

        self::assertEmpty($this->logger->cleanLogs());
    }

    public function testHandleWithApiNotAjaxRequest(): void
    {
        $request = Request::create('http://test.com/api/test');

        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn(null);

        $this->tokenStorage->expects(self::never())
            ->method('setToken');

        $this->apiRequestHelper->expects(self::once())
            ->method('isApiRequest')
            ->with('/api/test')
            ->willReturn(true);
        $this->csrfProtectedRequestHelper->expects(self::once())
            ->method('isCsrfProtectedRequest')
            ->with(self::identicalTo($request))
            ->willReturn(false);

        ($this->listener)($this->getRequestEvent($request));

        self::assertEmpty($this->logger->cleanLogs());
    }

    public function testHandleWithApiAjaxRequest(): void
    {
        $visitor = $this->getCustomerVisitor(4, 'someSessionId');

        $request = Request::create('http://test.com/api/test');

        $authenticatedToken = new AnonymousCustomerUserToken('Anonymous Customer User');
        $authenticatedToken->setVisitor($visitor);

        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn(null);

        $this->apiRequestHelper->expects(self::once())
            ->method('isApiRequest')
            ->with('/api/test')
            ->willReturn(true);
        $this->csrfProtectedRequestHelper->expects(self::once())
            ->method('isCsrfProtectedRequest')
            ->with(self::identicalTo($request))
            ->willReturn(true);

        $this->authenticationManager->expects(self::once())
            ->method('authenticate')
            ->willReturn($authenticatedToken);

        $this->tokenStorage->expects(self::once())
            ->method('setToken')
            ->with(self::identicalTo($authenticatedToken));

        ($this->listener)($this->getRequestEvent($request));

        self::assertEquals(
            [
                ['info', 'Populated the TokenStorage with an Anonymous Customer User Token.', []]
            ],
            $this->logger->cleanLogs()
        );
    }
}
