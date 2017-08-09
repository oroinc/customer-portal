<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Security\Firewall;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\DependencyInjection\Configuration;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;
use Oro\Bundle\CustomerBundle\Security\Firewall\AnonymousCustomerUserAuthenticationListener;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Component\Testing\Unit\EntityTrait;

class AnonymousCustomerUserAuthenticationListenerTest extends \PHPUnit_Framework_TestCase
{
    use EntityTrait;

    const VISITOR_CREDENTIALS = [4, 'someSessionId'];

    /**
     * @var AnonymousCustomerUserAuthenticationListener
     */
    protected $listener;

    /**
     * @var TokenStorageInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $tokenStorage;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var AuthenticationManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $authenticationManager;

    /**
     * @var ConfigManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configManager;

    protected function setUp()
    {
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->authenticationManager = $this->createMock(AuthenticationManagerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->configManager = $this->createMock(ConfigManager::class);

        $this->listener = new AnonymousCustomerUserAuthenticationListener(
            $this->tokenStorage,
            $this->authenticationManager,
            $this->logger,
            $this->configManager
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

        $newToken = new AnonymousCustomerUserToken('Anonymous Customer User', ['ROLE_FRONTEND_ANONYMOUS']);

        $visitor = $this->getEntity(CustomerVisitor::class, ['id' => 4, 'session_id' => 'someSessionId']);
        $newToken->setVisitor($visitor);

        $this->authenticationManager->expects($this->once())
            ->method('authenticate')
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
     * @return GetResponseEvent|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getEventMock()
    {
        return $this->getMockBuilder(GetResponseEvent::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
