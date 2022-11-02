<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Request;

use Oro\Bundle\FrontendBundle\Request\DynamicSessionHttpKernelDecorator;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\SecurityBundle\Tests\Unit\Request\ContainerStub;
use Oro\Component\Testing\ReflectionUtil;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class DynamicSessionHttpKernelDecoratorTest extends \PHPUnit\Framework\TestCase
{
    private const BACKEND_SESSION_OPTIONS = [
        'name'            => 'BACK',
        'cookie_path'     => '/admin',
        'cookie_lifetime' => 10
    ];

    private const FRONTEND_SESSION_OPTIONS = [
        'name'        => 'FRONT',
        'cookie_path' => '/'
    ];

    /** @var HttpKernel|\PHPUnit\Framework\MockObject\MockObject */
    private $kernel;

    /** @var ContainerInterface */
    private $container;

    /** @var FrontendHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $frontendHelper;

    /** @var DynamicSessionHttpKernelDecorator */
    private $kernelDecorator;

    protected function setUp(): void
    {
        $this->kernel = $this->createMock(HttpKernel::class);
        $this->container = new ContainerStub([
            'session.storage.options' => self::BACKEND_SESSION_OPTIONS
        ]);
        $this->frontendHelper = $this->createMock(FrontendHelper::class);

        $this->kernelDecorator = new DynamicSessionHttpKernelDecorator(
            $this->kernel,
            $this->container,
            $this->frontendHelper,
            self::FRONTEND_SESSION_OPTIONS
        );
    }

    private function getBackendSessionOptions(): ?array
    {
        return ReflectionUtil::getPropertyValue($this->kernelDecorator, 'backendSessionOptions');
    }

    public function testHandleForBackendRequest()
    {
        $request = Request::create('http://localhost/admin/test.php');
        $type = HttpKernelInterface::MASTER_REQUEST;
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
            $this->kernelDecorator->handle($request, $type, $catch)
        );

        self::assertEquals(
            self::BACKEND_SESSION_OPTIONS,
            $this->container->getParameter('session.storage.options')
        );

        self::assertSame(
            [
                'name' => 'BACK',
                'cookie_path' => '/admin',
                'cookie_lifetime' => 10
            ],
            $this->getBackendSessionOptions()
        );
    }

    public function testHandleForFrontendRequest()
    {
        $request = Request::create('http://localhost/test.php');
        $type = HttpKernelInterface::MASTER_REQUEST;
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
            $this->kernelDecorator->handle($request, $type, $catch)
        );

        self::assertEquals(
            array_replace(self::BACKEND_SESSION_OPTIONS, self::FRONTEND_SESSION_OPTIONS),
            $this->container->getParameter('session.storage.options')
        );
        self::assertEquals(
            self::BACKEND_SESSION_OPTIONS,
            $this->getBackendSessionOptions()
        );
    }

    public function testHandleForBackendRequestAfterFrontendRequest()
    {
        $this->frontendHelper->expects(self::exactly(2))
            ->method('isFrontendUrl')
            ->willReturnOnConsecutiveCalls(true, false);
        $this->kernel->expects(self::exactly(2))
            ->method('handle')
            ->willReturnOnConsecutiveCalls($this->createMock(Response::class), $this->createMock(Response::class));

        $this->kernelDecorator->handle(Request::create('http://localhost/test.php'));
        $this->kernelDecorator->handle(Request::create('http://localhost/admin/test.php'));

        self::assertEquals(
            self::BACKEND_SESSION_OPTIONS,
            $this->container->getParameter('session.storage.options')
        );
        self::assertEquals(
            self::BACKEND_SESSION_OPTIONS,
            $this->getBackendSessionOptions()
        );
    }

    public function testHandleForFrontendRequestAfterBackendRequest()
    {
        $this->frontendHelper->expects(self::exactly(2))
            ->method('isFrontendUrl')
            ->willReturnOnConsecutiveCalls(false, true);
        $this->kernel->expects(self::exactly(2))
            ->method('handle')
            ->willReturnOnConsecutiveCalls($this->createMock(Response::class), $this->createMock(Response::class));

        $this->kernelDecorator->handle(Request::create('http://localhost/admin/test.php'));
        $this->kernelDecorator->handle(Request::create('http://localhost/test.php'));

        self::assertEquals(
            array_replace(self::BACKEND_SESSION_OPTIONS, self::FRONTEND_SESSION_OPTIONS),
            $this->container->getParameter('session.storage.options')
        );
        self::assertEquals(
            self::BACKEND_SESSION_OPTIONS,
            $this->getBackendSessionOptions()
        );
    }

    public function testHandleForBackendRequestForApplicationInSubDir()
    {
        $request = $this->createMock(Request::class);
        $request->expects(self::any())
            ->method('getPathInfo')
            ->willReturn('/subDir');
        $request->expects(self::once())
            ->method('getBasePath')
            ->willReturn('/subDir');
        $type = HttpKernelInterface::MASTER_REQUEST;
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
            $this->kernelDecorator->handle($request, $type, $catch)
        );

        $this->assertEquals(
            '/subDir/admin',
            $this->container->getParameter('session.storage.options')['cookie_path']
        );

        self::assertSame(
            [
                'name' => 'BACK',
                'cookie_path' => '/admin',
                'cookie_lifetime' => 10
            ],
            $this->getBackendSessionOptions()
        );
    }

    public function testHandleForFrontendRequestForApplicationInSubDir()
    {
        $request = $this->createMock(Request::class);
        $request->expects(self::any())
            ->method('getPathInfo')
            ->willReturn('/subDir');
        $request->expects(self::once())
            ->method('getBasePath')
            ->willReturn('/subDir');
        $type = HttpKernelInterface::MASTER_REQUEST;
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
            $this->kernelDecorator->handle($request, $type, $catch)
        );

        self::assertEquals(
            [
                'name' => 'FRONT',
                'cookie_path' => '/subDir/',
                'cookie_lifetime' => 10
            ],
            $this->container->getParameter('session.storage.options')
        );
        self::assertEquals(
            self::BACKEND_SESSION_OPTIONS,
            $this->getBackendSessionOptions()
        );
    }

    public function testHandleForBackendRequestAfterFrontendRequestForApplicationInSubDir()
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
        $this->kernelDecorator->handle($request);
        // backend request
        $this->kernelDecorator->handle($request);

        self::assertEquals(
            [
                'name' => 'BACK',
                'cookie_path' => '/subDir/admin',
                'cookie_lifetime' => 10
            ],
            $this->container->getParameter('session.storage.options')
        );
        self::assertEquals(
            self::BACKEND_SESSION_OPTIONS,
            $this->getBackendSessionOptions()
        );
    }

    public function testHandleForFrontendRequestAfterBackendRequestForApplicationInSubDir()
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
        $this->kernelDecorator->handle($request);
        // frontend request
        $this->kernelDecorator->handle($request);

        self::assertEquals(
            [
                'name' => 'FRONT',
                'cookie_path' => '/subDir/',
                'cookie_lifetime' => 10
            ],
            $this->container->getParameter('session.storage.options')
        );
        self::assertEquals(
            self::BACKEND_SESSION_OPTIONS,
            $this->getBackendSessionOptions()
        );
    }
}
