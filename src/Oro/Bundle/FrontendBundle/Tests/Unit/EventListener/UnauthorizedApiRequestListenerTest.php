<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\EventListener;

use Oro\Bundle\ApiBundle\Request\ApiRequestHelper;
use Oro\Bundle\ApiBundle\Request\Rest\RequestActionHandler;
use Oro\Bundle\DistributionBundle\Handler\ApplicationState;
use Oro\Bundle\FrontendBundle\EventListener\UnauthorizedApiRequestListener;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Component\Testing\Unit\TestContainerBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class UnauthorizedApiRequestListenerTest extends \PHPUnit\Framework\TestCase
{
    /** @var RequestActionHandler|\PHPUnit\Framework\MockObject\MockObject */
    private $backendHandler;

    /** @var RequestActionHandler|\PHPUnit\Framework\MockObject\MockObject */
    private $frontendHandler;

    /** @var ApiRequestHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $apiRequestHelper;

    /** @var UnauthorizedApiRequestListener */
    private $listener;

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

        $this->listener = new UnauthorizedApiRequestListener(
            $container,
            $this->apiRequestHelper,
            $frontendHelper,
            $backendPrefix
        );
    }

    private function getEvent(Request $request, Response $response): ResponseEvent
    {
        return new ResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MASTER_REQUEST,
            $response
        );
    }

    public function testGetSubscribedServices(): void
    {
        self::assertEquals(
            [
                'handler'          => RequestActionHandler::class,
                'frontend_handler' => RequestActionHandler::class
            ],
            UnauthorizedApiRequestListener::getSubscribedServices()
        );
    }

    public function testForAuthorizedRequest(): void
    {
        $request = Request::create('http://test.com/api/products/1');
        $response = new Response('', Response::HTTP_OK);

        $this->apiRequestHelper->expects(self::never())
            ->method('isApiRequest');
        $this->backendHandler->expects(self::never())
            ->method('handleUnhandledError');
        $this->frontendHandler->expects(self::never())
            ->method('handleUnhandledError');

        $event = $this->getEvent($request, $response);
        $this->listener->onKernelResponse($event);
        self::assertSame($response, $event->getResponse());
    }

    public function testForUnauthorizedNotApiRequest(): void
    {
        $request = Request::create('http://test.com/product/view/1');
        $response = new Response('', Response::HTTP_UNAUTHORIZED);

        $this->apiRequestHelper->expects(self::once())
            ->method('isApiRequest')
            ->with($request->getPathInfo())
            ->willReturn(false);
        $this->backendHandler->expects(self::never())
            ->method('handleUnhandledError');
        $this->frontendHandler->expects(self::never())
            ->method('handleUnhandledError');

        $event = $this->getEvent($request, $response);
        $this->listener->onKernelResponse($event);
        self::assertSame($response, $event->getResponse());
    }

    public function testForUnauthorizedBackendApiRequestWithoutWwwAuthenticateHeader(): void
    {
        $request = Request::create('http://test.com/admin/api/products/1');
        $response = new Response('', Response::HTTP_UNAUTHORIZED);

        $expectedUnauthorizedHttpException = new HttpException(
            Response::HTTP_UNAUTHORIZED,
            '',
            null,
            [],
            0
        );
        $newResponse = new Response('', Response::HTTP_UNAUTHORIZED);

        $this->apiRequestHelper->expects(self::once())
            ->method('isApiRequest')
            ->with('/api/products/1')
            ->willReturn(true);
        $this->backendHandler->expects(self::once())
            ->method('handleUnhandledError')
            ->with(self::identicalTo($request), $expectedUnauthorizedHttpException)
            ->willReturn($newResponse);
        $this->frontendHandler->expects(self::never())
            ->method('handleUnhandledError');

        $event = $this->getEvent($request, $response);
        $this->listener->onKernelResponse($event);
        self::assertSame($newResponse, $event->getResponse());
    }


    public function testForUnauthorizedFrontendApiRequestWithoutWwwAuthenticateHeader(): void
    {
        $request = Request::create('http://test.com/api/products/1');
        $response = new Response('', Response::HTTP_UNAUTHORIZED);

        $expectedUnauthorizedHttpException = new HttpException(
            Response::HTTP_UNAUTHORIZED,
            '',
            null,
            [],
            0
        );
        $newResponse = new Response('', Response::HTTP_UNAUTHORIZED);

        $this->apiRequestHelper->expects(self::once())
            ->method('isApiRequest')
            ->with($request->getPathInfo())
            ->willReturn(true);
        $this->backendHandler->expects(self::never())
            ->method('handleUnhandledError');
        $this->frontendHandler->expects(self::once())
            ->method('handleUnhandledError')
            ->with(self::identicalTo($request), $expectedUnauthorizedHttpException)
            ->willReturn($newResponse);

        $event = $this->getEvent($request, $response);
        $this->listener->onKernelResponse($event);
        self::assertSame($newResponse, $event->getResponse());
    }

    public function testForUnauthorizedBackendApiRequestWithWwwAuthenticateHeader(): void
    {
        $request = Request::create('http://test.com/admin/api/products/1');
        $response = new Response('', Response::HTTP_UNAUTHORIZED);
        $response->headers->set('other', 'other header value');
        $response->headers->set('www-authenticate', 'www authenticate header value');

        $expectedUnauthorizedHttpException = new HttpException(
            Response::HTTP_UNAUTHORIZED,
            '',
            null,
            ['WWW-Authenticate' => 'www authenticate header value'],
            0
        );
        $newResponse = new Response('', Response::HTTP_UNAUTHORIZED);

        $this->apiRequestHelper->expects(self::once())
            ->method('isApiRequest')
            ->with('/api/products/1')
            ->willReturn(true);
        $this->backendHandler->expects(self::once())
            ->method('handleUnhandledError')
            ->with(self::identicalTo($request), $expectedUnauthorizedHttpException)
            ->willReturn($newResponse);
        $this->frontendHandler->expects(self::never())
            ->method('handleUnhandledError');

        $event = $this->getEvent($request, $response);
        $this->listener->onKernelResponse($event);
        self::assertSame($newResponse, $event->getResponse());
    }

    public function testForUnauthorizedFrontendApiRequestWithWwwAuthenticateHeader(): void
    {
        $request = Request::create('http://test.com/api/products/1');
        $response = new Response('', Response::HTTP_UNAUTHORIZED);
        $response->headers->set('other', 'other header value');
        $response->headers->set('www-authenticate', 'www authenticate header value');

        $expectedUnauthorizedHttpException = new HttpException(
            Response::HTTP_UNAUTHORIZED,
            '',
            null,
            ['WWW-Authenticate' => 'www authenticate header value'],
            0
        );
        $newResponse = new Response('', Response::HTTP_UNAUTHORIZED);

        $this->apiRequestHelper->expects(self::once())
            ->method('isApiRequest')
            ->with($request->getPathInfo())
            ->willReturn(true);
        $this->backendHandler->expects(self::never())
            ->method('handleUnhandledError');
        $this->frontendHandler->expects(self::once())
            ->method('handleUnhandledError')
            ->with(self::identicalTo($request), $expectedUnauthorizedHttpException)
            ->willReturn($newResponse);

        $event = $this->getEvent($request, $response);
        $this->listener->onKernelResponse($event);
        self::assertSame($newResponse, $event->getResponse());
    }

    public function testForUnauthorizedBackendApiRequestWithNotEmptyResponseContent(): void
    {
        $request = Request::create('http://test.com/admin/api/products/1');
        $response = new Response('test content', Response::HTTP_UNAUTHORIZED);

        $expectedUnauthorizedHttpException = new HttpException(
            Response::HTTP_UNAUTHORIZED,
            'test content',
            null,
            [],
            0
        );
        $newResponse = new Response('', Response::HTTP_UNAUTHORIZED);

        $this->apiRequestHelper->expects(self::once())
            ->method('isApiRequest')
            ->with('/api/products/1')
            ->willReturn(true);
        $this->backendHandler->expects(self::once())
            ->method('handleUnhandledError')
            ->with(self::identicalTo($request), $expectedUnauthorizedHttpException)
            ->willReturn($newResponse);
        $this->frontendHandler->expects(self::never())
            ->method('handleUnhandledError');

        $event = $this->getEvent($request, $response);
        $this->listener->onKernelResponse($event);
        self::assertSame($newResponse, $event->getResponse());
    }

    public function testForUnauthorizedFrontendApiRequestWithNotEmptyResponseContent(): void
    {
        $request = Request::create('http://test.com/api/products/1');
        $response = new Response('test content', Response::HTTP_UNAUTHORIZED);

        $expectedUnauthorizedHttpException = new HttpException(
            Response::HTTP_UNAUTHORIZED,
            'test content',
            null,
            [],
            0
        );
        $newResponse = new Response('', Response::HTTP_UNAUTHORIZED);

        $this->apiRequestHelper->expects(self::once())
            ->method('isApiRequest')
            ->with($request->getPathInfo())
            ->willReturn(true);
        $this->backendHandler->expects(self::never())
            ->method('handleUnhandledError');
        $this->frontendHandler->expects(self::once())
            ->method('handleUnhandledError')
            ->with(self::identicalTo($request), $expectedUnauthorizedHttpException)
            ->willReturn($newResponse);

        $event = $this->getEvent($request, $response);
        $this->listener->onKernelResponse($event);
        self::assertSame($newResponse, $event->getResponse());
    }

    public function testForUnauthorizedBackendApiRequestWhenResponseContentIsFalse(): void
    {
        $request = Request::create('http://test.com/admin/api/products/1');
        $response = new StreamedResponse(null, Response::HTTP_UNAUTHORIZED);

        $expectedUnauthorizedHttpException = new HttpException(
            Response::HTTP_UNAUTHORIZED,
            '',
            null,
            [],
            0
        );
        $newResponse = new Response('', Response::HTTP_UNAUTHORIZED);

        $this->apiRequestHelper->expects(self::once())
            ->method('isApiRequest')
            ->with('/api/products/1')
            ->willReturn(true);
        $this->backendHandler->expects(self::once())
            ->method('handleUnhandledError')
            ->with(self::identicalTo($request), $expectedUnauthorizedHttpException)
            ->willReturnCallback(function (Request $request, HttpException $error) use ($newResponse) {
                self::assertSame('', $error->getMessage());

                return $newResponse;
            });
        $this->frontendHandler->expects(self::never())
            ->method('handleUnhandledError');

        $event = $this->getEvent($request, $response);
        $this->listener->onKernelResponse($event);
        self::assertSame($newResponse, $event->getResponse());
    }

    public function testForUnauthorizedFrontendApiRequestWhenResponseContentIsFalse(): void
    {
        $request = Request::create('http://test.com/api/products/1');
        $response = new StreamedResponse(null, Response::HTTP_UNAUTHORIZED);

        $expectedUnauthorizedHttpException = new HttpException(
            Response::HTTP_UNAUTHORIZED,
            '',
            null,
            [],
            0
        );
        $newResponse = new Response('', Response::HTTP_UNAUTHORIZED);

        $this->apiRequestHelper->expects(self::once())
            ->method('isApiRequest')
            ->with($request->getPathInfo())
            ->willReturn(true);
        $this->backendHandler->expects(self::never())
            ->method('handleUnhandledError');
        $this->frontendHandler->expects(self::once())
            ->method('handleUnhandledError')
            ->with(self::identicalTo($request), $expectedUnauthorizedHttpException)
            ->willReturnCallback(function (Request $request, HttpException $error) use ($newResponse) {
                self::assertSame('', $error->getMessage());

                return $newResponse;
            });

        $event = $this->getEvent($request, $response);
        $this->listener->onKernelResponse($event);
        self::assertSame($newResponse, $event->getResponse());
    }
}
