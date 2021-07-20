<?php

namespace Oro\Bundle\RedirectBundle\Tests\Unit\Security;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\FrontendBundle\EventListener\GuestAccessRequestListener;
use Oro\Bundle\FrontendBundle\GuestAccess\GuestAccessDecisionMakerInterface;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Routing\RouterInterface;

class GuestAccessRequestListenerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @internal
     */
    private const REQUEST_URL = '/random-url';

    private const REQUEST_API_URL = '/api/random-url';

    /**
     * @internal
     */
    private const REDIRECT_URL = '/customer/user/login';

    /**
     * @var TokenAccessorInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $tokenAccessor;

    /**
     * @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject
     */
    private $configManager;

    /**
     * @var GuestAccessDecisionMakerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $guestAccessDecisionMaker;

    /**
     * @var RouterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $router;

    /**
     * @var GetResponseEvent|\PHPUnit\Framework\MockObject\MockObject
     */
    private $event;

    /**
     * @var Request|\PHPUnit\Framework\MockObject\MockObject
     */
    private $request;

    /**
     * @var GuestAccessRequestListener
     */
    private $listener;

    protected function setUp(): void
    {
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);
        $this->configManager = $this->createMock(ConfigManager::class);
        $this->guestAccessDecisionMaker = $this->createMock(GuestAccessDecisionMakerInterface::class);
        $this->router = $this->createMock(RouterInterface::class);
        $this->event = $this->createMock(GetResponseEvent::class);
        $this->request = $this->createMock(Request::class);

        $this->listener = new GuestAccessRequestListener(
            $this->tokenAccessor,
            $this->configManager,
            $this->guestAccessDecisionMaker,
            $this->router,
            '/api/'
        );
    }

    public function testOnKernelRequestIfGuestAccessAllowed()
    {
        $this->event
            ->expects($this->once())
            ->method('isMasterRequest')
            ->willReturn(true);

        $this->tokenAccessor
            ->expects($this->once())
            ->method('hasUser')
            ->willReturn(false);

        $this->configManager
            ->expects($this->once())
            ->method('get')
            ->with('oro_frontend.guest_access_enabled')
            ->willReturn(true);

        $this->guestAccessDecisionMaker
            ->expects($this->never())
            ->method('decide');

        $this->listener->onKernelRequest($this->event);
    }

    public function testOnKernelRequestIfNotMasterRequest()
    {
        $this->event
            ->expects($this->once())
            ->method('isMasterRequest')
            ->willReturn(false);

        $this->tokenAccessor
            ->expects($this->never())
            ->method('hasUser');

        $this->listener->onKernelRequest($this->event);
    }

    public function testOnKernelRequestIfAuthenticated()
    {
        $this->event
            ->expects($this->once())
            ->method('isMasterRequest')
            ->willReturn(true);

        $this->tokenAccessor
            ->expects($this->once())
            ->method('hasUser')
            ->willReturn(true);

        $this->configManager
            ->expects($this->never())
            ->method('get');

        $this->listener->onKernelRequest($this->event);
    }

    public function testOnKernelRequestIfUrlAllowed()
    {
        $this->mockEventIsSupported();
        $this->mockRequest();

        $this->guestAccessDecisionMaker
            ->expects($this->once())
            ->method('decide')
            ->with(self::REQUEST_URL)
            ->willReturn(GuestAccessDecisionMakerInterface::URL_ALLOW);

        $this->event
            ->expects($this->never())
            ->method('setResponse');

        $this->listener->onKernelRequest($this->event);
    }

    public function testOnKernelRequestIfUrlDisallowed()
    {
        $this->mockEventIsSupported();
        $this->mockRequest();

        $this->guestAccessDecisionMaker
            ->expects($this->once())
            ->method('decide')
            ->with(self::REQUEST_URL)
            ->willReturn(GuestAccessDecisionMakerInterface::URL_DISALLOW);

        $this->router
            ->expects($this->once())
            ->method('generate')
            ->with('oro_customer_customer_user_security_login')
            ->willReturn(self::REDIRECT_URL);

        $this->event
            ->expects($this->once())
            ->method('setResponse')
            ->with($this->callback([$this, 'eventSetRedirectResponseCallback']));

        $this->listener->onKernelRequest($this->event);
    }

    public function testOnKernelRequestApiRequestWithNotOptionMethodIfUrlDisallowed()
    {
        $this->mockEventIsSupported();
        $this->mockRequest(self::REQUEST_API_URL);

        $this->guestAccessDecisionMaker
            ->expects($this->once())
            ->method('decide')
            ->with(self::REQUEST_API_URL)
            ->willReturn(GuestAccessDecisionMakerInterface::URL_DISALLOW);

        $this->router
            ->expects($this->once())
            ->method('generate')
            ->with('oro_customer_customer_user_security_login')
            ->willReturn(self::REDIRECT_URL);

        $this->event
            ->expects($this->once())
            ->method('setResponse')
            ->with($this->callback([$this, 'eventSetResponseCallback']));

        $this->listener->onKernelRequest($this->event);
    }

    public function testOnKernelRequestApiRequestWithOptionMethodIfUrlDisallowed()
    {
        $this->mockEventIsSupported();
        $this->mockRequest(self::REQUEST_API_URL, 'OPTIONS');

        $this->guestAccessDecisionMaker
            ->expects($this->once())
            ->method('decide')
            ->with(self::REQUEST_API_URL)
            ->willReturn(GuestAccessDecisionMakerInterface::URL_DISALLOW);

        $this->event
            ->expects($this->never())
            ->method('setResponse');

        $this->listener->onKernelRequest($this->event);
    }

    public function testOnKernelRequestApiRequestIfUrlAllowed()
    {
        $this->mockEventIsSupported();
        $this->mockRequest(self::REQUEST_API_URL);

        $this->guestAccessDecisionMaker
            ->expects($this->once())
            ->method('decide')
            ->with(self::REQUEST_API_URL)
            ->willReturn(GuestAccessDecisionMakerInterface::URL_ALLOW);

        $this->event
            ->expects($this->never())
            ->method('setResponse');

        $this->listener->onKernelRequest($this->event);
    }

    /**
     * @param RedirectResponse $response
     *
     * @return bool
     */
    public function eventSetRedirectResponseCallback($response)
    {
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(self::REDIRECT_URL, $response->getTargetUrl());
        $this->assertEquals(302, $response->getStatusCode());

        return true;
    }

    /**
     * @param Response $response
     *
     * @return bool
     */
    public function eventSetResponseCallback(Response $response)
    {
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEmpty($response->getContent());
        $this->assertEquals(401, $response->getStatusCode());

        return true;
    }

    private function mockRequest(string $requestUrl = self::REQUEST_URL, string $requestMethod = 'GET')
    {
        $this->request
            ->expects($this->any())
            ->method('getMethod')
            ->willReturn($requestMethod);

        $this->event
            ->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->request);

        $this->request
            ->expects($this->any())
            ->method('getPathInfo')
            ->willReturn($requestUrl);
    }

    private function mockEventIsSupported()
    {
        $this->event
            ->expects($this->once())
            ->method('isMasterRequest')
            ->willReturn(true);

        $this->tokenAccessor
            ->expects($this->once())
            ->method('hasUser')
            ->willReturn(false);

        $this->configManager
            ->expects($this->once())
            ->method('get')
            ->with('oro_frontend.guest_access_enabled')
            ->willReturn(false);
    }
}
