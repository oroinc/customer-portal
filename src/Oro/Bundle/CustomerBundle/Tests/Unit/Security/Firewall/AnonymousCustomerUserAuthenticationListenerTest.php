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
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AnonymousCustomerUserAuthenticationListenerTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    const VISITOR_CREDENTIALS = [4, 'someSessionId'];

    /**
     * @var AnonymousCustomerUserAuthenticationListener
     */
    protected $listener;

    /**
     * @var TokenStorageInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $tokenStorage;

    /**
     * @var LoggerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $logger;

    /**
     * @var AuthenticationManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $authenticationManager;

    /**
     * @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $configManager;

    /** @var WebsiteManager|\PHPUnit\Framework\MockObject\MockObject */
    protected $websiteManager;

    /** @var CacheProvider|\PHPUnit_Framework_MockObject_MockObject */
    private $cacheProvider;

    /** @var CsrfRequestManager|\PHPUnit_Framework_MockObject_MockObject */
    private $csrfRequestManager;

    protected function setUp(): void
    {
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->authenticationManager = $this->createMock(AuthenticationManagerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->configManager = $this->createMock(ConfigManager::class);
        $this->websiteManager = $this->createMock(WebsiteManager::class);
        $this->cacheProvider = $this->createMock(CacheProvider::class);
        $this->csrfRequestManager = $this->createMock(CsrfRequestManager::class);

        $this->listener = new AnonymousCustomerUserAuthenticationListener(
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
    public function testHandle($token)
    {
        if (null === $token) {
            $this->cacheProvider->expects($this->once())
                ->method('fetch')
                ->with(AnonymousCustomerUserAuthenticationListener::CACHE_KEY)
                ->willReturn(false);
        } else {
            $this->cacheProvider->expects($this->never())
                ->method('fetch');
        }

        $request = new Request();
        $request->cookies->set(
            AnonymousCustomerUserAuthenticationListener::COOKIE_NAME,
            base64_encode(json_encode(self::VISITOR_CREDENTIALS))
        );

        $event = $this->getEventMock();
        $event->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $newToken = new AnonymousCustomerUserToken('Anonymous Customer User');

        $visitor = $this->getEntity(CustomerVisitor::class, ['id' => 4, 'session_id' => 'someSessionId']);
        $newToken->setVisitor($visitor);

        $currentWebsite = new WebsiteStub();
        $currentWebsite->setGuestRole(new CustomerUserRole('TEST_ANONYMOUS_ROLE'));
        $this->websiteManager->expects($this->once())
            ->method('getCurrentWebsite')
            ->willReturn($currentWebsite);

        $this->authenticationManager->expects($this->once())
            ->method('authenticate')
            ->with($this->callback(function (TokenInterface $token) {
                $roles = $token->getRoles();
                if (count($roles) !== 1) {
                    return false;
                }
                $role = reset($roles);
                if ($role->getRole() !== 'ROLE_FRONTEND_TEST_ANONYMOUS_ROLE') {
                    return false;
                }
                return true;
            }))
            ->willReturn($newToken);

        $this->tokenStorage->expects($this->once())
            ->method('setToken')
            ->with($newToken);

        $this->logger->expects($this->once())
            ->method('info')
            ->with('Populated the TokenStorage with an Anonymous Customer User Token.');

        $this->configManager->expects($this->once())
            ->method('get')
            ->with('oro_customer.customer_visitor_cookie_lifetime_days')
            ->willReturn(30);

        $this->listener->handle($event);

        /** @var Cookie $resultCookie */
        $resultCookie = $request->attributes->get(AnonymousCustomerUserAuthenticationListener::COOKIE_ATTR_NAME);
        $this->assertEquals(AnonymousCustomerUserAuthenticationListener::COOKIE_NAME, $resultCookie->getName());
    }

    public function testHandleWithBrokenGuestRole()
    {
        $currentWebsite = new WebsiteStub();
        $currentWebsite->setGuestRole(new CustomerUserRole());
        $this->websiteManager->expects($this->once())
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

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $request = new Request();

        $event = $this->getEventMock();
        $event->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn(null);

        $this->authenticationManager->expects($this->once())
            ->method('authenticate')
            ->with($createdToken)
            ->willReturn($authenticatedToken);

        $this->tokenStorage->expects($this->once())
            ->method('setToken')
            ->with($authenticatedToken);

        $this->logger->expects($this->once())
            ->method('info')
            ->with('Populated the TokenStorage with an Anonymous Customer User Token.');

        $this->listener->handle($event);

        /** @var Cookie $resultCookie */
        $this->assertNull($request->attributes->get(AnonymousCustomerUserAuthenticationListener::COOKIE_ATTR_NAME));
    }

    /**
     * @return array
     */
    public function handleDataProvider()
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

    public function testHandleWithAuthenticationException()
    {
        $event = $this->getEventMock();
        $event->expects($this->once())
            ->method('getRequest')
            ->willReturn(new Request());
        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn(null);

        $exception = new AuthenticationException;
        $this->authenticationManager->expects($this->once())
            ->method('authenticate')
            ->willThrowException($exception);

        $this->logger->expects($this->once())
            ->method('info')
            ->with('Customer User anonymous authentication failed.', ['exception' => $exception]);

        $this->listener->handle($event);
    }

    public function testHandleWithUnsupportedToken()
    {
        $request = new Request();
        $request->cookies->set(
            AnonymousCustomerUserAuthenticationListener::COOKIE_NAME,
            base64_encode(json_encode(self::VISITOR_CREDENTIALS))
        );

        $event = $this->getEventMock();
        $event->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($this->createMock(TokenInterface::class));

        $this->authenticationManager->expects($this->never())
            ->method('authenticate');

        $this->listener->handle($event);
    }

    public function testHandleWithCachedToken()
    {
        $token = new AnonymousCustomerUserToken('User');
        $this->cacheProvider->expects($this->once())
            ->method('fetch')
            ->with(AnonymousCustomerUserAuthenticationListener::CACHE_KEY)
            ->willReturn($token);

        $this->tokenStorage->expects($this->once())
            ->method('setToken')
            ->with($token);

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn(null);

        $this->listener->handle($this->getEventMock());
    }

    public function testHandleWithAlreadyAuthenticatedAnonymousToken()
    {
        $token = new AnonymousCustomerUserToken(
            'User',
            [new CustomerUserRole('anon')],
            new CustomerVisitor(),
            new Organization()
        );
        $request = new Request();

        $event = $this->getEventMock();

        $event->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->tokenStorage->expects($this->never())
            ->method('setToken');

        $this->listener->handle($event);
    }

    public function testHandleWithApiGetAjaxRequest()
    {
        $newToken = new AnonymousCustomerUserToken('Anonymous Customer User');
        $visitor = $this->getEntity(CustomerVisitor::class, ['id' => 4, 'session_id' => 'someSessionId']);
        $newToken->setVisitor($visitor);

        $request = new Request([], [], ['_route' => 'foo']);
        $request->server = new ServerBag(['REQUEST_URI' => 'http://test.com/api/test']);
        $request->headers->set('X-CSRF-Header', 1);

        $session = $this->createMock(SessionInterface::class);
        $session->expects($this->once())
            ->method('getName')
            ->willReturn('TEST_SESSION_ID');

        $request->setSession($session);
        $request->cookies->add(['TEST_SESSION_ID'=> 'o595fqdg5214u4e4nfcs3uc923']);
        $request->setMethod('GET');

        $event = $this->getEventMock();

        $event->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn(null);

        $this->authenticationManager->expects($this->once())
            ->method('authenticate')
            ->willReturn($newToken);

        $this->tokenStorage->expects($this->once())
            ->method('setToken')
            ->with($newToken);

        $this->listener->handle($event);
    }

    public function testHandleWithApiNotAjaxRequest()
    {
        $request = new Request([], [], ['_route' => 'foo']);
        $request->server = new ServerBag(['REQUEST_URI' => 'http://test.com/api/test']);
        $request->setMethod('GET');

        $event = $this->getEventMock();

        $event->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn(null);

        $this->tokenStorage->expects($this->never())
            ->method('setToken');

        $this->listener->handle($event);
    }

    public function testHandleWithApiPostAjaxRequest()
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

        $event = $this->getEventMock();

        $event->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn(null);

        $this->csrfRequestManager->expects($this->once())
            ->method('isRequestTokenValid')
            ->with($request, false)
            ->willReturn(true);

        $this->authenticationManager->expects($this->once())
            ->method('authenticate')
            ->willReturn($newToken);

        $this->tokenStorage->expects($this->once())
            ->method('setToken')
            ->with($newToken);

        $this->listener->handle($event);
    }

    /**
     * @return GetResponseEvent|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getEventMock()
    {
        return $this->getMockBuilder(GetResponseEvent::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
