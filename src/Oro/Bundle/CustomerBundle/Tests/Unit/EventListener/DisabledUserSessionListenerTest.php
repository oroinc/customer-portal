<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\EventListener;

use Oro\Bundle\CustomerBundle\EventListener\DisabledUserSessionListener;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Exception\LockedException;
use Symfony\Component\Security\Http\Logout\LogoutUrlGenerator;

class DisabledUserSessionListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LogoutUrlGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logoutUrlGenerator;

    /**
     * @var FrontendHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $frontendHelper;

    /**
     * @var DisabledUserSessionListener
     */
    private $listener;

    public function setUp()
    {
        $this->logoutUrlGenerator = $this->createMock(LogoutUrlGenerator::class);
        $this->frontendHelper = $this->createMock(FrontendHelper::class);
        $this->listener = new DisabledUserSessionListener($this->logoutUrlGenerator, $this->frontendHelper);
    }

    public function testExceptionOnFrontend()
    {
        $event = new GetResponseForExceptionEvent(
            $this->createMock(HttpKernelInterface::class),
            $this->createMock(Request::class),
            HttpKernelInterface::MASTER_REQUEST,
            new LockedException()
        );
        $this->frontendHelper->method('isFrontendRequest')->willReturn(true);
        $logoutUrl = '/logout';
        $this->logoutUrlGenerator->method('getLogoutUrl')->willReturn($logoutUrl);
        $this->listener->onKernelException($event);
        $response = $event->getResponse();
        static::assertInstanceOf(RedirectResponse::class, $response);
        static::assertEquals($logoutUrl, $response->getTargetUrl());
    }

    public function testExceptionOnBackend()
    {
        $event = new GetResponseForExceptionEvent(
            $this->createMock(HttpKernelInterface::class),
            $this->createMock(Request::class),
            HttpKernelInterface::MASTER_REQUEST,
            new LockedException()
        );
        $this->frontendHelper->method('isFrontendRequest')->willReturn(false);
        $this->logoutUrlGenerator->expects(static::never())->method('getLogoutUrl');
        $this->listener->onKernelException($event);
        static::assertNull($event->getResponse());
    }
}
