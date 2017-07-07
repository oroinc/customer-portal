<?php

namespace Oro\Bundle\RedirectBundle\Tests\Unit\Security;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\FrontendBundle\EventListener\GuestAccessRequestListener;
use Oro\Bundle\FrontendBundle\GuestAccess\GuestAccessDecisionMakerInterface;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;

class GuestAccessRequestListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @internal
     */
    const REQUEST_URL = '/random-url';

    /**
     * @internal
     */
    const REDIRECT_URL = '/customer/user/login';

    /**
     * @internal
     */
    const REDIRECT_STATUS_CODE = 302;

    /**
     * @var TokenAccessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $tokenAccessor;

    /**
     * @var ConfigManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configManager;

    /**
     * @var GuestAccessDecisionMakerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $guestAccessDecisionMaker;

    /**
     * @var RouterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $router;

    /**
     * @var GetResponseEvent|\PHPUnit_Framework_MockObject_MockObject
     */
    private $event;

    /**
     * @var Request|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var GuestAccessRequestListener
     */
    private $listener;

    protected function setUp()
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
            $this->router
        );
    }

    public function testOnKernelRequestIfGuestAccessAllowed()
    {
        $this->event
            ->expects(static::once())
            ->method('isMasterRequest')
            ->willReturn(true);

        $this->tokenAccessor
            ->expects(static::once())
            ->method('hasUser')
            ->willReturn(false);

        $this->configManager
            ->expects(static::once())
            ->method('get')
            ->with('oro_frontend.guest_access_enabled')
            ->willReturn(true);

        $this->guestAccessDecisionMaker
            ->expects(static::never())
            ->method('decide');

        $this->listener->onKernelRequest($this->event);
    }

    public function testOnKernelRequestIfNotMasterRequest()
    {
        $this->event
            ->expects(static::once())
            ->method('isMasterRequest')
            ->willReturn(false);

        $this->tokenAccessor
            ->expects(static::never())
            ->method('hasUser');

        $this->listener->onKernelRequest($this->event);
    }

    public function testOnKernelRequestIfAuthenticated()
    {
        $this->event
            ->expects(static::once())
            ->method('isMasterRequest')
            ->willReturn(true);

        $this->tokenAccessor
            ->expects(static::once())
            ->method('hasUser')
            ->willReturn(true);

        $this->configManager
            ->expects(static::never())
            ->method('get');

        $this->listener->onKernelRequest($this->event);
    }

    public function testOnKernelRequestIfUrlAllowed()
    {
        $this->mockEventIsSupported();
        $this->mockRequest();

        $this->guestAccessDecisionMaker
            ->expects(static::once())
            ->method('decide')
            ->with(self::REQUEST_URL)
            ->willReturn(GuestAccessDecisionMakerInterface::URL_ALLOW);

        $this->event
            ->expects(static::never())
            ->method('setResponse');

        $this->listener->onKernelRequest($this->event);
    }

    public function testOnKernelRequestIfUrlDisallowed()
    {
        $this->mockEventIsSupported();
        $this->mockRequest();

        $this->guestAccessDecisionMaker
            ->expects(static::once())
            ->method('decide')
            ->with(self::REQUEST_URL)
            ->willReturn(GuestAccessDecisionMakerInterface::URL_DISALLOW);

        $this->router
            ->expects(static::once())
            ->method('generate')
            ->with('oro_customer_customer_user_security_login')
            ->willReturn(self::REDIRECT_URL);

        $this->event
            ->expects(static::once())
            ->method('setResponse')
            ->with(static::callback([$this, 'eventSetResponseCallback']));

        $this->listener->onKernelRequest($this->event);
    }

    /**
     * @param RedirectResponse $response
     *
     * @return bool
     */
    public function eventSetResponseCallback($response)
    {
        static::assertInstanceOf(RedirectResponse::class, $response);
        static::assertEquals(self::REDIRECT_URL, $response->getTargetUrl());
        static::assertEquals(self::REDIRECT_STATUS_CODE, $response->getStatusCode());

        return true;
    }

    private function mockRequest()
    {
        $this->event
            ->expects(static::once())
            ->method('getRequest')
            ->willReturn($this->request);

        $this->request
            ->expects(static::once())
            ->method('getPathInfo')
            ->willReturn(self::REQUEST_URL);
    }

    private function mockEventIsSupported()
    {
        $this->event
            ->expects(static::once())
            ->method('isMasterRequest')
            ->willReturn(true);

        $this->tokenAccessor
            ->expects(static::once())
            ->method('hasUser')
            ->willReturn(false);

        $this->configManager
            ->expects(static::once())
            ->method('get')
            ->with('oro_frontend.guest_access_enabled')
            ->willReturn(false);
    }
}
