<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Request;

use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\FrontendBundle\Request\StorefrontSessionHttpKernelDecorator;
use Oro\Bundle\SecurityBundle\Request\SessionHttpKernelDecorator;
use Oro\Bundle\SecurityBundle\Request\SessionStorageOptionsManipulator;
use Oro\Bundle\SecurityBundle\Tests\Unit\Request\ContainerStub;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class StorefrontSessionHttpKernelDecoratorTest extends TestCase
{
    private const array BACKEND_SESSION_OPTIONS = [
        'name' => 'BACK',
        'cookie_path' => '/admin',
        'cookie_lifetime' => 10,
    ];

    private const array FRONTEND_SESSION_OPTIONS = [
        'name' => 'FRONT',
        'cookie_path' => '/',
    ];

    private HttpKernel|MockObject $kernel;

    private ContainerStub|ContainerInterface $container;

    private FrontendHelper|MockObject $frontendHelper;

    private SessionHttpKernelDecorator $sessionHttpKernelDecorator;

    #[\Override]
    protected function setUp(): void
    {
        $this->kernel = $this->createMock(HttpKernel::class);
        $this->container = new ContainerStub([
            'oro_security.session.storage.options' => self::BACKEND_SESSION_OPTIONS,
            'session.storage.options' => self::BACKEND_SESSION_OPTIONS,
        ]);
        $sessionStorageOptionsManipulator = new SessionStorageOptionsManipulator($this->container);
        $this->frontendHelper = $this->createMock(FrontendHelper::class);

        $storefrontSessionHttpKernelDecorator = new StorefrontSessionHttpKernelDecorator(
            $this->kernel,
            $sessionStorageOptionsManipulator,
            $this->frontendHelper,
            self::FRONTEND_SESSION_OPTIONS
        );

        $this->sessionHttpKernelDecorator = new SessionHttpKernelDecorator(
            $storefrontSessionHttpKernelDecorator,
            $sessionStorageOptionsManipulator
        );
    }

    public function testHandleForBackendRequest(): void
    {
        $request = Request::create('http://localhost/admin/test.php');
        $type = HttpKernelInterface::MAIN_REQUEST;
        $catch = true;
        $response = $this->createMock(Response::class);

        $this->frontendHelper->expects(self::once())
            ->method('isFrontendUrl')
            ->with($request->getPathInfo())
            ->willReturn(false);
        $this->kernel->expects(self::once())
            ->method('handle')
            ->with(self::identicalTo($request), $type, $catch)
            ->willReturn($response);

        self::assertSame(
            $response,
            $this->sessionHttpKernelDecorator->handle($request, $type, $catch)
        );

        self::assertEquals(
            self::BACKEND_SESSION_OPTIONS,
            $this->container->getParameter('session.storage.options')
        );
    }

    public function testHandleForFrontendRequest(): void
    {
        $request = Request::create('http://localhost/test.php');
        $type = HttpKernelInterface::MAIN_REQUEST;
        $catch = true;
        $response = $this->createMock(Response::class);

        $this->frontendHelper->expects(self::once())
            ->method('isFrontendUrl')
            ->with($request->getPathInfo())
            ->willReturn(true);
        $this->kernel->expects(self::once())
            ->method('handle')
            ->with(self::identicalTo($request), $type, $catch)
            ->willReturn($response);

        self::assertSame(
            $response,
            $this->sessionHttpKernelDecorator->handle($request, $type, $catch)
        );

        self::assertEquals(
            array_replace(self::BACKEND_SESSION_OPTIONS, self::FRONTEND_SESSION_OPTIONS),
            $this->container->getParameter('session.storage.options')
        );
    }

    public function testHandleForBackendRequestAfterFrontendRequest(): void
    {
        $this->frontendHelper->expects(self::exactly(2))
            ->method('isFrontendUrl')
            ->willReturnOnConsecutiveCalls(true, false);
        $this->kernel->expects(self::exactly(2))
            ->method('handle')
            ->willReturnOnConsecutiveCalls($this->createMock(Response::class), $this->createMock(Response::class));

        $this->sessionHttpKernelDecorator->handle(Request::create('http://localhost/test.php'));
        $this->sessionHttpKernelDecorator->handle(Request::create('http://localhost/admin/test.php'));

        self::assertEquals(
            self::BACKEND_SESSION_OPTIONS,
            $this->container->getParameter('session.storage.options')
        );
    }

    public function testHandleForFrontendRequestAfterBackendRequest(): void
    {
        $this->frontendHelper->expects(self::exactly(2))
            ->method('isFrontendUrl')
            ->willReturnOnConsecutiveCalls(false, true);
        $this->kernel->expects(self::exactly(2))
            ->method('handle')
            ->willReturnOnConsecutiveCalls($this->createMock(Response::class), $this->createMock(Response::class));

        $this->sessionHttpKernelDecorator->handle(Request::create('http://localhost/admin/test.php'));
        $this->sessionHttpKernelDecorator->handle(Request::create('http://localhost/test.php'));

        self::assertEquals(
            array_replace(self::BACKEND_SESSION_OPTIONS, self::FRONTEND_SESSION_OPTIONS),
            $this->container->getParameter('session.storage.options')
        );
    }

    public function testHandleForBackendRequestForApplicationInSubDir(): void
    {
        $request = $this->createMock(Request::class);
        $request->expects(self::any())
            ->method('getPathInfo')
            ->willReturn('/subDir');
        $request->expects(self::once())
            ->method('getBasePath')
            ->willReturn('/subDir');
        $type = HttpKernelInterface::MAIN_REQUEST;
        $catch = true;
        $response = $this->createMock(Response::class);

        $this->frontendHelper->expects(self::once())
            ->method('isFrontendUrl')
            ->with($request->getPathInfo())
            ->willReturn(false);
        $this->kernel->expects(self::once())
            ->method('handle')
            ->with(self::identicalTo($request), $type, $catch)
            ->willReturn($response);

        self::assertSame(
            $response,
            $this->sessionHttpKernelDecorator->handle($request, $type, $catch)
        );

        self::assertEquals(
            [
                'name' => 'BACK',
                'cookie_path' => '/subDir/admin',
                'cookie_lifetime' => 10,
            ],
            $this->container->getParameter('session.storage.options')
        );
    }

    public function testHandleForFrontendRequestForApplicationInSubDir(): void
    {
        $request = $this->createMock(Request::class);
        $request
            ->method('getPathInfo')
            ->willReturn('/subDir');
        $request
            ->method('getBasePath')
            ->willReturn('/subDir');
        $type = HttpKernelInterface::MAIN_REQUEST;
        $catch = true;
        $response = $this->createMock(Response::class);

        $this->frontendHelper->expects(self::once())
            ->method('isFrontendUrl')
            ->with($request->getPathInfo())
            ->willReturn(true);
        $this->kernel->expects(self::once())
            ->method('handle')
            ->with(self::identicalTo($request), $type, $catch)
            ->willReturn($response);

        self::assertSame(
            $response,
            $this->sessionHttpKernelDecorator->handle($request, $type, $catch)
        );

        self::assertEquals(
            [
                'name' => 'FRONT',
                'cookie_path' => '/subDir/',
                'cookie_lifetime' => 10,
            ],
            $this->container->getParameter('session.storage.options')
        );
    }

    public function testHandleForBackendRequestAfterFrontendRequestForApplicationInSubDir(): void
    {
        $request = $this->createMock(Request::class);
        $request->expects(self::any())
            ->method('getPathInfo')
            ->willReturn('/subDir');
        $request->expects(self::exactly(2))
            ->method('getBasePath')
            ->willReturn('/subDir');

        $this->frontendHelper->expects(self::exactly(2))
            ->method('isFrontendUrl')
            ->willReturnOnConsecutiveCalls(true, false);
        $this->kernel->expects(self::exactly(2))
            ->method('handle')
            ->willReturnOnConsecutiveCalls($this->createMock(Response::class), $this->createMock(Response::class));

        // frontend request
        $this->sessionHttpKernelDecorator->handle($request);
        // backend request
        $this->sessionHttpKernelDecorator->handle($request);

        self::assertEquals(
            [
                'name' => 'BACK',
                'cookie_path' => '/subDir/admin',
                'cookie_lifetime' => 10,
            ],
            $this->container->getParameter('session.storage.options')
        );
    }

    public function testHandleForFrontendRequestAfterBackendRequestForApplicationInSubDir(): void
    {
        $request = $this->createMock(Request::class);
        $request->expects(self::any())
            ->method('getPathInfo')
            ->willReturn('/subDir');
        $request->expects(self::exactly(2))
            ->method('getBasePath')
            ->willReturn('/subDir');

        $this->frontendHelper->expects(self::exactly(2))
            ->method('isFrontendUrl')
            ->willReturnOnConsecutiveCalls(false, true);
        $this->kernel->expects(self::exactly(2))
            ->method('handle')
            ->willReturnOnConsecutiveCalls($this->createMock(Response::class), $this->createMock(Response::class));

        // backend request
        $this->sessionHttpKernelDecorator->handle($request);
        // frontend request
        $this->sessionHttpKernelDecorator->handle($request);

        self::assertEquals(
            [
                'name' => 'FRONT',
                'cookie_path' => '/subDir/',
                'cookie_lifetime' => 10,
            ],
            $this->container->getParameter('session.storage.options')
        );
    }
}
