<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\EventListener;

use Oro\Bundle\ApiBundle\Request\ApiRequestHelper;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\FrontendBundle\EventListener\GuestAccessRequestListener;
use Oro\Bundle\FrontendBundle\GuestAccess\GuestAccessDecisionMakerInterface;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\RouterInterface;

class GuestAccessRequestListenerTest extends \PHPUnit\Framework\TestCase
{
    private const REQUEST_URL = '/random-url';
    private const REQUEST_API_URL = '/api/random-url';
    private const REDIRECT_URL = '/customer/user/login';

    /** @var TokenAccessorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $tokenAccessor;

    /** @var ConfigManager */
    private $configManager;

    /** @var GuestAccessDecisionMakerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $guestAccessDecisionMaker;

    /** @var RouterInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $router;

    /** @var ApiRequestHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $apiRequestHelper;

    /** @var RequestEvent|\PHPUnit\Framework\MockObject\MockObject */
    private $event;

    /** @var Request|\PHPUnit\Framework\MockObject\MockObject */
    private $request;

    /** @var GuestAccessRequestListener */
    private $listener;

    protected function setUp(): void
    {
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);
        $this->configManager = $this->createMock(ConfigManager::class);
        $this->guestAccessDecisionMaker = $this->createMock(GuestAccessDecisionMakerInterface::class);
        $this->router = $this->createMock(RouterInterface::class);
        $this->apiRequestHelper = $this->createMock(ApiRequestHelper::class);
        $this->event = $this->createMock(RequestEvent::class);
        $this->request = $this->createMock(Request::class);

        $this->event->expects(self::any())
            ->method('getRequest')
            ->willReturn($this->request);

        $this->listener = new GuestAccessRequestListener(
            $this->tokenAccessor,
            $this->configManager,
            $this->guestAccessDecisionMaker,
            $this->router,
            $this->apiRequestHelper
        );
    }

    public function testOnKernelRequestIfGuestAccessAllowed(): void
    {
        $this->event->expects(self::once())
            ->method('isMasterRequest')
            ->willReturn(true);

        $this->tokenAccessor->expects(self::once())
            ->method('hasUser')
            ->willReturn(false);

        $this->configManager->expects(self::once())
            ->method('get')
            ->with('oro_frontend.guest_access_enabled')
            ->willReturn(true);

        $this->guestAccessDecisionMaker->expects(self::never())
            ->method('decide');

        $this->listener->onKernelRequest($this->event);
    }

    public function testOnKernelRequestIfNotMasterRequest(): void
    {
        $this->event->expects(self::once())
            ->method('isMasterRequest')
            ->willReturn(false);

        $this->tokenAccessor->expects(self::never())
            ->method('hasUser');

        $this->listener->onKernelRequest($this->event);
    }

    public function testOnKernelRequestIfAuthenticated(): void
    {
        $this->event->expects(self::once())
            ->method('isMasterRequest')
            ->willReturn(true);

        $this->tokenAccessor->expects(self::once())
            ->method('hasUser')
            ->willReturn(true);

        $this->configManager->expects(self::never())
            ->method('get');

        $this->listener->onKernelRequest($this->event);
    }

    public function testOnKernelRequestIfUrlAllowed(): void
    {
        $this->mockEventIsSupported();
        $this->mockRequest();

        $this->guestAccessDecisionMaker->expects(self::once())
            ->method('decide')
            ->with(self::REQUEST_URL)
            ->willReturn(GuestAccessDecisionMakerInterface::URL_ALLOW);

        $this->event->expects(self::never())
            ->method('setResponse');

        $this->listener->onKernelRequest($this->event);
    }

    public function testOnKernelRequestIfUrlDisallowed(): void
    {
        $this->mockEventIsSupported();
        $this->mockRequest();

        $this->guestAccessDecisionMaker->expects(self::once())
            ->method('decide')
            ->with(self::REQUEST_URL)
            ->willReturn(GuestAccessDecisionMakerInterface::URL_DISALLOW);

        $this->apiRequestHelper->expects(self::once())
            ->method('isApiRequest')
            ->with(self::REQUEST_URL)
            ->willReturn(false);

        $this->router->expects(self::once())
            ->method('generate')
            ->with('oro_customer_customer_user_security_login')
            ->willReturn(self::REDIRECT_URL);

        $this->event->expects(self::once())
            ->method('setResponse')
            ->with(self::callback([$this, 'eventSetRedirectResponseCallback']));

        $this->listener->onKernelRequest($this->event);
    }

    public function testOnKernelRequestApiRequestWithNotOptionMethodIfUrlDisallowed(): void
    {
        $this->mockEventIsSupported();
        $this->mockRequest(self::REQUEST_API_URL);

        $this->guestAccessDecisionMaker->expects(self::once())
            ->method('decide')
            ->with(self::REQUEST_API_URL)
            ->willReturn(GuestAccessDecisionMakerInterface::URL_DISALLOW);

        $this->apiRequestHelper->expects(self::once())
            ->method('isApiRequest')
            ->with(self::REQUEST_API_URL)
            ->willReturn(true);

        $this->router->expects(self::never())
            ->method('generate');

        $this->event->expects(self::once())
            ->method('setResponse')
            ->with(self::callback([$this, 'eventSetResponseCallback']));

        $this->listener->onKernelRequest($this->event);
    }

    public function testOnKernelRequestApiRequestWithOptionMethodIfUrlDisallowed(): void
    {
        $this->mockEventIsSupported();
        $this->mockRequest(self::REQUEST_API_URL, 'OPTIONS');

        $this->guestAccessDecisionMaker->expects(self::once())
            ->method('decide')
            ->with(self::REQUEST_API_URL)
            ->willReturn(GuestAccessDecisionMakerInterface::URL_DISALLOW);

        $this->event->expects(self::never())
            ->method('setResponse');

        $this->listener->onKernelRequest($this->event);
    }

    public function testOnKernelRequestApiRequestIfUrlAllowed(): void
    {
        $this->mockEventIsSupported();
        $this->mockRequest(self::REQUEST_API_URL);

        $this->guestAccessDecisionMaker->expects(self::once())
            ->method('decide')
            ->with(self::REQUEST_API_URL)
            ->willReturn(GuestAccessDecisionMakerInterface::URL_ALLOW);

        $this->event->expects(self::never())
            ->method('setResponse');

        $this->listener->onKernelRequest($this->event);
    }

    public function eventSetRedirectResponseCallback(RedirectResponse $response): bool
    {
        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertEquals(self::REDIRECT_URL, $response->getTargetUrl());
        self::assertEquals(302, $response->getStatusCode());

        return true;
    }

    public function eventSetResponseCallback(Response $response): bool
    {
        self::assertInstanceOf(Response::class, $response);
        self::assertEmpty($response->getContent());
        self::assertEquals(401, $response->getStatusCode());

        return true;
    }

    private function mockRequest(string $requestUrl = self::REQUEST_URL, string $requestMethod = 'GET'): void
    {
        $this->request->expects(self::any())
            ->method('getMethod')
            ->willReturn($requestMethod);

        $this->request->expects(self::any())
            ->method('getPathInfo')
            ->willReturn($requestUrl);
    }

    private function mockEventIsSupported(): void
    {
        $this->event->expects(self::once())
            ->method('isMasterRequest')
            ->willReturn(true);

        $this->tokenAccessor->expects(self::once())
            ->method('hasUser')
            ->willReturn(false);

        $this->configManager->expects(self::once())
            ->method('get')
            ->with('oro_frontend.guest_access_enabled')
            ->willReturn(false);
    }
}
