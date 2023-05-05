<?php

namespace Oro\Bundle\FrontendBundle\ErrorRenderer;

use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\LayoutBundle\Layout\LayoutManager;
use Oro\Bundle\MaintenanceBundle\Maintenance\MaintenanceModeState;
use Oro\Bundle\MaintenanceBundle\Maintenance\Mode;
use Oro\Component\Layout\LayoutContext;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\ErrorHandler\ErrorRenderer\ErrorRendererInterface;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Provides the ability to render custom error pages via the layout engine on the storefront
 * in non-debug mode, otherwise falls back to a decorated error renderer, e.g. TwigErrorRenderer.
 */
class FrontendErrorRenderer implements ErrorRendererInterface, ServiceSubscriberInterface
{
    private const EXCEPTION_ROUTE_NAME = 'oro_frontend_exception';

    private ContainerInterface $container;
    private ErrorRendererInterface $fallbackErrorRenderer;
    private ErrorRendererInterface $baseErrorRenderer;
    private bool $debug;

    public function __construct(
        ContainerInterface $container,
        ErrorRendererInterface $fallbackErrorRenderer,
        ErrorRendererInterface $baseErrorRenderer,
        bool $debug = false
    ) {
        $this->container = $container;
        $this->fallbackErrorRenderer = $fallbackErrorRenderer;
        $this->baseErrorRenderer = $baseErrorRenderer;
        $this->debug = $debug;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices(): array
    {
        return [
            RequestStack::class,
            FrontendHelper::class,
            LayoutManager::class,
            TranslatorInterface::class,
            LoggerInterface::class,
            Mode::class,
            MaintenanceModeState::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function render(\Throwable $exception): FlattenException
    {
        $request = $this->getRequestStack()->getCurrentRequest();
        if (null !== $request && $this->isLayoutRendering($request)) {
            $flattenException = $this->baseErrorRenderer->render($exception);
            $error = $this->renderError($flattenException, $request);
            if (null !== $error) {
                $flattenException->setAsString($error);
            }

            return $flattenException;
        }

        return $this->fallbackErrorRenderer->render($exception);
    }

    private function isLayoutRendering(Request $request): bool
    {
        return
            $this->getFrontendHelper()->isFrontendUrl($request->getPathInfo())
            && $request->getRequestFormat() === 'html'
            && !$this->showException($request)
            && !$this->isCircularHandlingException()
            && !$this->getModeState()->isOn();
    }

    private function showException(Request $request): bool
    {
        return
            $this->debug
            && $request->attributes->getBoolean('showException', true);
    }

    private function isCircularHandlingException(): bool
    {
        $parentRequest = $this->getRequestStack()->getParentRequest();

        return
            null !== $parentRequest
            && $parentRequest->get('_route') === self::EXCEPTION_ROUTE_NAME;
    }

    private function renderError(FlattenException $exception, Request $request): ?string
    {
        $statusCode = $exception->getStatusCode();
        $context = new LayoutContext([
            'data' => [
                'status_code' => $statusCode,
                'status_text' => $this->getStatusText($statusCode)
            ]
        ]);
        $context->set('route_name', self::EXCEPTION_ROUTE_NAME);

        $requestForLayout = $this->getRequestForLayout($request);

        // emulate original request to render valid error page via the layout engine
        $requestStack = $this->getRequestStack();
        $requestStack->pop();
        $requestStack->push($requestForLayout);
        // render an error page via the layout engine
        try {
            return $this->getLayoutManager()->getLayout($context)->render();
        } catch (\Throwable $e) {
            $this->getLogger()->error(
                'Cannot render layout template for an error page, because of errors in some layout templates.',
                ['exception'=> $e, 'statusCode' => $statusCode]
            );
        } finally {
            // restore a sub-request request in the request stack
            $requestStack->pop();
            $requestStack->push($request);
        }

        return null;
    }

    private function getRequestForLayout(Request $request): Request
    {
        $request = clone $request;

        $parentRequest = $this->getRequestStack()->getParentRequest();
        if (null !== $parentRequest) {
            $request->query->add($parentRequest->query->all());
            $request->request->add($parentRequest->request->all());
            $request->attributes->add($parentRequest->attributes->all());
            $request->cookies->add($parentRequest->cookies->all());
            $request->files->add($parentRequest->files->all());
            $request->server->add($parentRequest->server->all());
        }

        return $request;
    }

    private function getStatusText(int $code): string
    {
        return $this->getTranslator()->trans(sprintf('oro_frontend.exception.status_text.%d', $code));
    }

    private function getRequestStack(): RequestStack
    {
        return $this->container->get(RequestStack::class);
    }

    private function getFrontendHelper(): FrontendHelper
    {
        return $this->container->get(FrontendHelper::class);
    }

    private function getLayoutManager(): LayoutManager
    {
        return $this->container->get(LayoutManager::class);
    }

    private function getTranslator(): TranslatorInterface
    {
        return $this->container->get(TranslatorInterface::class);
    }

    private function getLogger(): LoggerInterface
    {
        return $this->container->get(LoggerInterface::class);
    }

    private function getModeState(): MaintenanceModeState
    {
        return $this->container->get(MaintenanceModeState::class);
    }
}
