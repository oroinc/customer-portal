<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Security\Firewall;

use Doctrine\Common\Cache\CacheProvider;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;
use Oro\Bundle\CustomerBundle\Security\Firewall\AnonymousCustomerUserAuthenticationListener;
use Oro\Bundle\CustomerBundle\Security\Firewall\CustomerVisitorCookieFactory;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\CustomerBundle\Tests\Unit\Entity\Stub\WebsiteStub;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\Csrf\CsrfRequestManager;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Component\Testing\Unit\EntityTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ServerBag;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AnonymousCustomerUserAuthenticationListenerTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    private const VISITOR_CREDENTIALS = [4, 'someSessionId'];

    private AnonymousCustomerUserAuthenticationListener $listener;

    private TokenStorageInterface|\PHPUnit\Framework\MockObject\MockObject $tokenStorage;

    private LoggerInterface|\PHPUnit\Framework\MockObject\MockObject $logger;

    private AuthenticationManagerInterface|\PHPUnit\Framework\MockObject\MockObject $authenticationManager;

    private ConfigManager|\PHPUnit\Framework\MockObject\MockObject $configManager;

    private WebsiteManager|\PHPUnit\Framework\MockObject\MockObject $websiteManager;

    private CacheProvider|\PHPUnit\Framework\MockObject\MockObject $cacheProvider;

    private CsrfRequestManager|\PHPUnit\Framework\MockObject\MockObject $csrfRequestManager;

    private \PHPUnit\Framework\MockObject\MockObject|RequestEvent $event;

    protected function setUp(): void
    {
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->authenticationManager = $this->createMock(AuthenticationManagerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->configManager = $this->createMock(ConfigManager::class);
        $this->websiteManager = $this->createMock(WebsiteManager::class);
        $this->cacheProvider = $this->createMock(CacheProvider::class);
        $this->csrfRequestManager = $this->createMock(CsrfRequestManager::class);
        $this->event = $this->createMock(RequestEvent::class);

        $this->listener = new AnonymousCustomerUserAuthenticationlistener(
            $this->tokenStorage,
            $this->authenticationManager,
            $this->logger,
            $this->websiteManager,
            $this->cacheProvider,
            $this->csrfRequestManager,
            '^/api/',
            new CustomerVisitorCookieFactory('auto', true, $this->configManager, Cookie::SAMESITE_STRICT)
        );
    }

    /**
     * @dataProvider handleDataProvider
     *
     * @param null|AnonymousCustomerUserToken $token
     */
    public function testHandle($token): void
    {
        if (null === $token) {
            $this->cacheProvider->expects(self::once())
                ->method('fetch')
                ->with(AnonymousCustomerUserAuthenticationListener::CACHE_KEY)
                ->willReturn(false);
        } else {
            $this->cacheProvider->expects(self::never())
                ->method('fetch');
        }

        $request = new Request();
        $request->cookies->set(
            AnonymousCustomerUserAuthenticationListener::COOKIE_NAME,
            base64_encode(json_encode(self::VISITOR_CREDENTIALS))
        );

        $this->event->expects(self::once())
            ->method('getRequest')
            ->willReturn($request);

        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn($token);

        $newToken = new AnonymousCustomerUserToken('Anonymous Customer User');

        $visitor = $this->getEntity(CustomerVisitor::class, ['id' => 4, 'session_id' => 'someSessionId']);
        $newToken->setVisitor($visitor);

        $currentWebsite = new WebsiteStub();
        $currentWebsite->setGuestRole(new CustomerUserRole('TEST_ANONYMOUS_ROLE'));
        $this->websiteManager->expects(self::once())
            ->method('getCurrentWebsite')
            ->willReturn($currentWebsite);

        $this->authenticationManager->expects(self::once())
            ->method('authenticate')
            ->with(
                self::callback(
                    function (TokenInterface $token) {
                        $roleNames = $token->getRoleNames();
                        if (count($roleNames) !== 1) {
                            return false;
                        }
                        $roleName = reset($roleNames);
                        if ($roleName !== 'ROLE_FRONTEND_TEST_ANONYMOUS_ROLE') {
                            return false;
                        }

                        return true;
                    }
                )
            )
            ->willReturn($newToken);

        $this->tokenStorage->expects(self::once())
            ->method('setToken')
            ->with($newToken);

        $this->logger->expects(self::once())
            ->method('info')
            ->with('Populated the TokenStorage with an Anonymous Customer User Token.');

        $this->configManager->expects(self::once())
            ->method('get')
            ->with('oro_customer.customer_visitor_cookie_lifetime_days')
            ->willReturn(30);

        ($this->listener)($this->event);

        /** @var Cookie $resultCookie */
        $resultCookie = $request->attributes->get(AnonymousCustomerUserAuthenticationListener::COOKIE_ATTR_NAME);
        self::assertEquals(AnonymousCustomerUserAuthenticationListener::COOKIE_NAME, $resultCookie->getName());
    }

    public function testHandleWithBrokenGuestRole(): void
    {
        $currentWebsite = new WebsiteStub();
        $currentWebsite->setGuestRole(new CustomerUserRole());
        $this->websiteManager->expects(self::once())
            ->method('getCurrentWebsite')
            ->willReturn($currentWebsite);

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

        $this->event->expects(self::once())
            ->method('getRequest')
            ->willReturn($request);

        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn(null);

        $this->authenticationManager->expects(self::once())
            ->method('authenticate')
            ->with($createdToken)
            ->willReturn($authenticatedToken);

        $this->tokenStorage->expects(self::once())
            ->method('setToken')
            ->with($authenticatedToken);

        $this->logger->expects(self::once())
            ->method('info')
            ->with('Populated the TokenStorage with an Anonymous Customer User Token.');

        ($this->listener)($this->event);

        /** @var Cookie $resultCookie */
        self::assertNull($request->attributes->get(AnonymousCustomerUserAuthenticationListener::COOKIE_ATTR_NAME));
    }

    /**
     * @return array
     */
    public function handleDataProvider(): array
    {
        return [
            'null token' => [
                'token' => null,
            ],
            'AnonymousCustomerUserToken token' => [
                'token' => new AnonymousCustomerUserToken('User'),
            ],
        ];
    }

    public function testHandleWithAuthenticationException(): void
    {
        $this->event->expects(self::once())
            ->method('getRequest')
            ->willReturn(new Request());
        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn(null);

        $exception = new AuthenticationException;
        $this->authenticationManager->expects(self::once())
            ->method('authenticate')
            ->willThrowException($exception);

        $this->logger->expects(self::once())
            ->method('info')
            ->with('Customer User anonymous authentication failed.', ['exception' => $exception]);

        ($this->listener)($this->event);
    }

    public function testHandleWithUnsupportedToken(): void
    {
        $request = new Request();
        $request->cookies->set(
            AnonymousCustomerUserAuthenticationListener::COOKIE_NAME,
            base64_encode(json_encode(self::VISITOR_CREDENTIALS))
        );

        $this->event->expects(self::once())
            ->method('getRequest')
            ->willReturn($request);

        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn($this->createMock(TokenInterface::class));

        $this->authenticationManager->expects(self::never())
            ->method('authenticate');

        ($this->listener)($this->event);
    }

    public function testHandleWithCachedToken(): void
    {
        $token = new AnonymousCustomerUserToken('User');
        $this->cacheProvider->expects(self::once())
            ->method('fetch')
            ->with(AnonymousCustomerUserAuthenticationListener::CACHE_KEY)
            ->willReturn($token);

        $this->tokenStorage->expects(self::once())
            ->method('setToken')
            ->with($token);

        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn(null);

        ($this->listener)($this->event);
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

        $this->event->expects(self::once())
            ->method('getRequest')
            ->willReturn($request);

        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn($token);

        $this->tokenStorage->expects(self::never())
            ->method('setToken');

        ($this->listener)($this->event);
    }

    public function testHandleWithApiGetAjaxRequest(): void
    {
        $newToken = new AnonymousCustomerUserToken('Anonymous Customer User');
        $visitor = $this->getEntity(CustomerVisitor::class, ['id' => 4, 'session_id' => 'someSessionId']);
        $newToken->setVisitor($visitor);

        $request = new Request([], [], ['_route' => 'foo']);
        $request->server = new ServerBag(['REQUEST_URI' => 'http://test.com/api/test']);
        $request->headers->set('X-CSRF-Header', 1);

        $session = $this->createMock(SessionInterface::class);
        $session->expects(self::once())
            ->method('getName')
            ->willReturn('TEST_SESSION_ID');

        $request->setSession($session);
        $request->cookies->add(['TEST_SESSION_ID'=> 'o595fqdg5214u4e4nfcs3uc923']);
        $request->setMethod('GET');

        $this->event->expects(self::once())
            ->method('getRequest')
            ->willReturn($request);

        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn(null);

        $this->authenticationManager->expects(self::once())
            ->method('authenticate')
            ->willReturn($newToken);

        $this->tokenStorage->expects(self::once())
            ->method('setToken')
            ->with($newToken);

        ($this->listener)($this->event);
    }

    public function testHandleWithApiNotAjaxRequest(): void
    {
        $request = new Request([], [], ['_route' => 'foo']);
        $request->server = new ServerBag(['REQUEST_URI' => 'http://test.com/api/test']);
        $request->setMethod('GET');

        $this->event->expects(self::once())
            ->method('getRequest')
            ->willReturn($request);

        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn(null);

        $this->tokenStorage->expects(self::never())
            ->method('setToken');

        ($this->listener)($this->event);
    }

    public function testHandleWithApiPostAjaxRequest(): void
    {
        $newToken = new AnonymousCustomerUserToken('Anonymous Customer User');
        $visitor = $this->getEntity(CustomerVisitor::class, ['id' => 4, 'session_id' => 'someSessionId']);
        $newToken->setVisitor($visitor);

        $request = new Request([], [], ['_route' => 'foo']);
        $request->server = new ServerBag(['REQUEST_URI' => 'http://test.com/api/test']);
        $request->headers->set('X-CSRF-Header', 1);

        $session = $this->createMock(SessionInterface::class);
        $session->expects(self::any())
            ->method('getName')
            ->willReturn('TEST_SESSION_ID');

        $request->setSession($session);
        $request->cookies->add(['TEST_SESSION_ID'=> 'o595fqdg5214u4e4nfcs3uc923']);
        $request->setMethod('POST');

        $this->event->expects(self::once())
            ->method('getRequest')
            ->willReturn($request);

        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn(null);

        $this->csrfRequestManager->expects(self::once())
            ->method('isRequestTokenValid')
            ->with($request, false)
            ->willReturn(true);

        $this->authenticationManager->expects(self::once())
            ->method('authenticate')
            ->willReturn($newToken);

        $this->tokenStorage->expects(self::once())
            ->method('setToken')
            ->with($newToken);

        ($this->listener)($this->event);
    }
}
