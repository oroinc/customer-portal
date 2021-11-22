<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\EventListener;

use Oro\Bundle\CustomerBundle\EventListener\DisabledUserSessionListener;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Exception\LockedException;
use Symfony\Component\Security\Http\Logout\LogoutUrlGenerator;

class DisabledUserSessionListenerTest extends \PHPUnit\Framework\TestCase
{
    private LogoutUrlGenerator|\PHPUnit\Framework\MockObject\MockObject $logoutUrlGenerator;

    private FrontendHelper|\PHPUnit\Framework\MockObject\MockObject $frontendHelper;

    private DisabledUserSessionListener $listener;

    protected function setUp(): void
    {
        $this->logoutUrlGenerator = $this->createMock(LogoutUrlGenerator::class);
        $this->frontendHelper = $this->createMock(FrontendHelper::class);

        $this->listener = new DisabledUserSessionListener($this->logoutUrlGenerator, $this->frontendHelper);
    }

    public function testExceptionOnFrontend(): void
    {
        $request = Request::create('/test');
        $event = new ExceptionEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MASTER_REQUEST,
            new LockedException()
        );

        $this->frontendHelper->expects(self::once())
            ->method('isFrontendUrl')
            ->with($request->getPathInfo())
            ->willReturn(true);
        $logoutUrl = '/logout';
        $this->logoutUrlGenerator->expects(self::once())
            ->method('getLogoutUrl')
            ->willReturn($logoutUrl);

        $this->listener->onKernelException($event);
        /** @var RedirectResponse $response */
        $response = $event->getResponse();
        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertEquals($logoutUrl, $response->getTargetUrl());
    }

    public function testExceptionOnBackend(): void
    {
        $request = Request::create('/admin/test');
        $event = new ExceptionEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MASTER_REQUEST,
            new LockedException()
        );

        $this->frontendHelper->expects(self::once())
            ->method('isFrontendUrl')
            ->with($request->getPathInfo())
            ->willReturn(false);
        $this->logoutUrlGenerator->expects(self::never())
            ->method('getLogoutUrl');

        $this->listener->onKernelException($event);
        self::assertNull($event->getResponse());
    }
}
