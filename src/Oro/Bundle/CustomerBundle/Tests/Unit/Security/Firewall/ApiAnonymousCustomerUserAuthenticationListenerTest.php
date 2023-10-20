<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Security\Firewall;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Model\InMemoryCustomerVisitor;
use Oro\Bundle\CustomerBundle\Security\AnonymousCustomerUserRolesProvider;
use Oro\Bundle\CustomerBundle\Security\Firewall\ApiAnonymousCustomerUserAuthenticationDecisionMaker;
use Oro\Bundle\CustomerBundle\Security\Firewall\ApiAnonymousCustomerUserAuthenticationListener;
use Oro\Bundle\CustomerBundle\Security\Token\ApiAnonymousCustomerUserToken;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Component\Testing\Logger\BufferingLogger;
use Oro\Component\Testing\ReflectionUtil;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class ApiAnonymousCustomerUserAuthenticationListenerTest extends \PHPUnit\Framework\TestCase
{
    /** @var TokenStorageInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $tokenStorage;

    /** @var AuthenticationManagerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $authenticationManager;

    /** @var AnonymousCustomerUserRolesProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $rolesProvider;

    /** @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject */
    private $configManager;

    /** @var ApiAnonymousCustomerUserAuthenticationDecisionMaker|\PHPUnit\Framework\MockObject\MockObject */
    private $decisionMaker;

    /** @var BufferingLogger */
    private $logger;

    /** @var ApiAnonymousCustomerUserAuthenticationListener */
    private $listener;

    protected function setUp(): void
    {
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->authenticationManager = $this->createMock(AuthenticationManagerInterface::class);
        $this->rolesProvider = $this->createMock(AnonymousCustomerUserRolesProvider::class);
        $this->configManager = $this->createMock(ConfigManager::class);
        $this->decisionMaker = $this->createMock(ApiAnonymousCustomerUserAuthenticationDecisionMaker::class);
        $this->logger = new BufferingLogger();

        $this->listener = new ApiAnonymousCustomerUserAuthenticationListener(
            $this->tokenStorage,
            $this->authenticationManager,
            $this->rolesProvider,
            '^/api/',
            $this->configManager,
            $this->decisionMaker,
            $this->logger
        );
    }

    private function getRequestEvent(Request $request): RequestEvent
    {
        return new RequestEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );
    }

    public function testHandleWhenNoToken(): void
    {
        $visitor = new InMemoryCustomerVisitor();

        $request = Request::create('http://test.com/api/test');

        $authenticatedToken = new ApiAnonymousCustomerUserToken('API Anonymous Customer User');
        $authenticatedToken->setVisitor($visitor);

        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn(null);

        $this->configManager->expects(self::once())
            ->method('get')
            ->with('oro_customer.non_authenticated_visitors_api')
            ->willReturn(true);

        $this->decisionMaker->expects(self::once())
            ->method('isAnonymousCustomerUserAllowed')
            ->with(self::identicalTo($request))
            ->willReturn(true);

        $this->rolesProvider->expects(self::once())
            ->method('getRoles')
            ->willReturn(['TEST_ANONYMOUS_ROLE']);

        $this->authenticationManager->expects(self::once())
            ->method('authenticate')
            ->willReturnCallback(function (ApiAnonymousCustomerUserToken $token) use ($authenticatedToken) {
                self::assertEquals('API Anonymous Customer User', $token->getUser());
                self::assertEquals(['TEST_ANONYMOUS_ROLE'], $token->getRoleNames());

                return $authenticatedToken;
            });

        $this->tokenStorage->expects(self::once())
            ->method('setToken')
            ->with(self::identicalTo($authenticatedToken));

        ($this->listener)($this->getRequestEvent($request));
        self::assertSame($authenticatedToken, ReflectionUtil::getPropertyValue($this->listener, 'rememberedToken'));

        self::assertEquals(
            [
                ['info', 'Populated the TokenStorage with an API Anonymous Customer User Token.', []]
            ],
            $this->logger->cleanLogs()
        );
    }

    public function testHandleForApiAnonymousCustomerUserToken(): void
    {
        $token = new ApiAnonymousCustomerUserToken('User');

        $visitor = new InMemoryCustomerVisitor();

        $request = Request::create('http://test.com/test');

        $authenticatedToken = new ApiAnonymousCustomerUserToken('API Anonymous Customer User');
        $authenticatedToken->setVisitor($visitor);

        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn($token);

        $this->configManager->expects(self::never())
            ->method('get');

        $this->decisionMaker->expects(self::once())
            ->method('isAnonymousCustomerUserAllowed')
            ->with(self::identicalTo($request))
            ->willReturn(true);

        $this->rolesProvider->expects(self::once())
            ->method('getRoles')
            ->willReturn(['TEST_ANONYMOUS_ROLE']);

        $this->authenticationManager->expects(self::once())
            ->method('authenticate')
            ->willReturnCallback(function (ApiAnonymousCustomerUserToken $token) use ($authenticatedToken) {
                self::assertEquals('API Anonymous Customer User', $token->getUser());
                self::assertEquals(['TEST_ANONYMOUS_ROLE'], $token->getRoleNames());

                return $authenticatedToken;
            });

        $this->tokenStorage->expects(self::once())
            ->method('setToken')
            ->with(self::identicalTo($authenticatedToken));

        ($this->listener)($this->getRequestEvent($request));
        self::assertSame($authenticatedToken, ReflectionUtil::getPropertyValue($this->listener, 'rememberedToken'));

        self::assertEquals(
            [
                ['info', 'Populated the TokenStorage with an API Anonymous Customer User Token.', []]
            ],
            $this->logger->cleanLogs()
        );
    }

    public function testHandleWithRememberedToken(): void
    {
        $rememberedToken = new ApiAnonymousCustomerUserToken('User');
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

        $createdToken = new ApiAnonymousCustomerUserToken('API Anonymous Customer User');
        $authenticatedToken = new ApiAnonymousCustomerUserToken('API Anonymous Customer User');

        $request = Request::create('http://test.com/api/test');

        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn(null);

        $this->configManager->expects(self::once())
            ->method('get')
            ->with('oro_customer.non_authenticated_visitors_api')
            ->willReturn(true);

        $this->decisionMaker->expects(self::once())
            ->method('isAnonymousCustomerUserAllowed')
            ->with(self::identicalTo($request))
            ->willReturn(true);

        $this->rolesProvider->expects(self::once())
            ->method('getRoles')
            ->willReturn([]);

        $this->authenticationManager->expects(self::once())
            ->method('authenticate')
            ->with($createdToken)
            ->willReturn($authenticatedToken);

        $this->tokenStorage->expects(self::once())
            ->method('setToken')
            ->with(self::identicalTo($authenticatedToken));

        ($this->listener)($this->getRequestEvent($request));

        self::assertEquals(
            [
                ['info', 'Populated the TokenStorage with an API Anonymous Customer User Token.', []]
            ],
            $this->logger->cleanLogs()
        );
    }

    public function testHandleWithAuthenticationException(): void
    {
        $request = Request::create('http://test.com/api/test');

        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn(null);

        $this->configManager->expects(self::once())
            ->method('get')
            ->with('oro_customer.non_authenticated_visitors_api')
            ->willReturn(true);

        $this->decisionMaker->expects(self::once())
            ->method('isAnonymousCustomerUserAllowed')
            ->with(self::identicalTo($request))
            ->willReturn(true);

        $this->rolesProvider->expects(self::once())
            ->method('getRoles')
            ->willReturn(['TEST_ANONYMOUS_ROLE']);

        $exception = new AuthenticationException();
        $this->authenticationManager->expects(self::once())
            ->method('authenticate')
            ->willThrowException($exception);

        ($this->listener)($this->getRequestEvent($request));

        self::assertEquals(
            [
                ['info', 'API Customer User anonymous authentication failed.', ['exception' => $exception]]
            ],
            $this->logger->cleanLogs()
        );
    }

    public function testHandleWithUnsupportedToken(): void
    {
        $request = new Request();

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
        $token = new ApiAnonymousCustomerUserToken(
            'User',
            [new CustomerUserRole('anon')],
            new InMemoryCustomerVisitor(),
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

    public function testHandleWhenAnonymousCustomerUserNotAllowed(): void
    {
        $request = Request::create('http://test.com/api/test');

        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn(null);

        $this->tokenStorage->expects(self::never())
            ->method('setToken');

        $this->configManager->expects(self::once())
            ->method('get')
            ->with('oro_customer.non_authenticated_visitors_api')
            ->willReturn(true);

        $this->decisionMaker->expects(self::once())
            ->method('isAnonymousCustomerUserAllowed')
            ->with(self::identicalTo($request))
            ->willReturn(false);

        ($this->listener)($this->getRequestEvent($request));

        self::assertEmpty($this->logger->cleanLogs());
    }

    public function testHandleWhenAccessToNonAuthenticatedVisitorsDenied(): void
    {
        $request = Request::create('http://test.com/api/test');

        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn(null);

        $this->tokenStorage->expects(self::never())
            ->method('setToken');

        $this->configManager->expects(self::once())
            ->method('get')
            ->with('oro_customer.non_authenticated_visitors_api')
            ->willReturn(false);

        $this->decisionMaker->expects(self::never())
            ->method('isAnonymousCustomerUserAllowed');

        ($this->listener)($this->getRequestEvent($request));

        self::assertEmpty($this->logger->cleanLogs());
    }

    public function testHandleWithNotApiRequest(): void
    {
        $request = Request::create('http://test.com/test');

        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn(null);

        $this->tokenStorage->expects(self::never())
            ->method('setToken');

        $this->configManager->expects(self::never())
            ->method('get');

        $this->decisionMaker->expects(self::never())
            ->method('isAnonymousCustomerUserAllowed');

        ($this->listener)($this->getRequestEvent($request));

        self::assertEmpty($this->logger->cleanLogs());
    }
}
