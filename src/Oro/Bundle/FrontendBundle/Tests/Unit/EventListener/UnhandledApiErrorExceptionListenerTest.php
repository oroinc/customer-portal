<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\EventListener;

use Oro\Bundle\ApiBundle\Request\ApiRequestHelper;
use Oro\Bundle\ApiBundle\Request\Rest\RequestActionHandler;
use Oro\Bundle\DistributionBundle\Handler\ApplicationState;
use Oro\Bundle\FrontendBundle\EventListener\UnhandledApiErrorExceptionListener;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Component\Testing\Unit\TestContainerBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class UnhandledApiErrorExceptionListenerTest extends TestCase
{
    private RequestActionHandler&MockObject $backendHandler;
    private RequestActionHandler&MockObject $frontendHandler;
    private ApiRequestHelper&MockObject $apiRequestHelper;
    private UnhandledApiErrorExceptionListener $listener;

    #[\Override]
    protected function setUp(): void
    {
        $backendPrefix = '/admin';

        $this->backendHandler = $this->createMock(RequestActionHandler::class);
        $this->frontendHandler = $this->createMock(RequestActionHandler::class);
        $this->apiRequestHelper = $this->createMock(ApiRequestHelper::class);

        $applicationState = $this->createMock(ApplicationState::class);
        $applicationState->expects(self::any())
            ->method('isInstalled')
            ->willReturn(true);

        $frontendHelper = new FrontendHelper(
            $backendPrefix,
            $this->createMock(RequestStack::class),
            $applicationState
        );

        $container = TestContainerBuilder::create()
            ->add('handler', $this->backendHandler)
            ->add('frontend_handler', $this->frontendHandler)
            ->getContainer($this);

        $this->listener = new UnhandledApiErrorExceptionListener(
            $container,
            $this->apiRequestHelper,
            $frontendHelper,
            $backendPrefix
        );
    }

    private function getEvent(Request $request, \Throwable $exception): ExceptionEvent
    {
        return new ExceptionEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $exception
        );
    }

    public function testGetSubscribedServices(): void
    {
        self::assertEquals(
            [
                'handler'          => RequestActionHandler::class,
                'frontend_handler' => RequestActionHandler::class
            ],
            UnhandledApiErrorExceptionListener::getSubscribedServices()
        );
    }

    public function testForNotApiRequest(): void
    {
        $request = Request::create('http://test.com/product/view/1');

        $this->apiRequestHelper->expects(self::once())
            ->method('isApiRequest')
            ->with('/product/view/1')
            ->willReturn(false);
        $this->backendHandler->expects(self::never())
            ->method('handleUnhandledError');
        $this->frontendHandler->expects(self::never())
            ->method('handleUnhandledError');

        $event = $this->getEvent($request, new \Exception('some error'));
        $this->listener->onKernelException($event);
        self::assertNull($event->getResponse());
    }

    public function testForBackendApiRequest(): void
    {
        $request = Request::create('http://test.com/admin/api/products/1');
        $exception = new \Exception('some error');
        $response = $this->createMock(Response::class);

        $this->apiRequestHelper->expects(self::once())
            ->method('isApiRequest')
            ->with('/api/products/1')
            ->willReturn(true);
        $this->backendHandler->expects(self::once())
            ->method('handleUnhandledError')
            ->with(self::identicalTo($request), self::identicalTo($exception))
            ->willReturn($response);
        $this->frontendHandler->expects(self::never())
            ->method('handleUnhandledError');

        $event = $this->getEvent($request, $exception);
        $this->listener->onKernelException($event);
        self::assertSame($response, $event->getResponse());
    }

    public function testForFrontendApiRequest(): void
    {
        $request = Request::create('http://test.com/api/products/1');
        $exception = new \Exception('some error');
        $response = $this->createMock(Response::class);

        $this->apiRequestHelper->expects(self::once())
            ->method('isApiRequest')
            ->with('/api/products/1')
            ->willReturn(true);
        $this->backendHandler->expects(self::never())
            ->method('handleUnhandledError');
        $this->frontendHandler->expects(self::once())
            ->method('handleUnhandledError')
            ->with(self::identicalTo($request), self::identicalTo($exception))
            ->willReturn($response);

        $event = $this->getEvent($request, $exception);
        $this->listener->onKernelException($event);
        self::assertSame($response, $event->getResponse());
    }
}
