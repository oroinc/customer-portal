<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Security\Firewall;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;
use Oro\Bundle\CustomerBundle\Security\Firewall\AnonymousCustomerUserAuthenticationListener;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\CustomerBundle\Tests\Unit\Entity\Stub\WebsiteStub;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Component\Testing\Unit\EntityTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
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

    protected function setUp()
    {
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->authenticationManager = $this->createMock(AuthenticationManagerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->configManager = $this->createMock(ConfigManager::class);
        $this->websiteManager = $this->createMock(WebsiteManager::class);

        $this->listener = new AnonymousCustomerUserAuthenticationListener(
            $this->tokenStorage,
            $this->authenticationManager,
            $this->logger,
            $this->configManager,
            $this->websiteManager
        );
    }

    /**
     * @dataProvider handleDataProvider
     *
     * @param null|AnonymousCustomerUserToken $token
     */
    public function testHandle($token)
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
        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($this->createMock(TokenInterface::class));

        $this->authenticationManager->expects($this->never())
            ->method('authenticate');

        $this->listener->handle($this->getEventMock());
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
