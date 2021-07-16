<?php

namespace Oro\Bundle\FrontendBundle\Controller;

use FOS\RestBundle\Util\ExceptionValueMap;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\LayoutBundle\Layout\LayoutManager;
use Oro\Bundle\MaintenanceBundle\Maintenance\Mode;
use Oro\Bundle\UIBundle\Controller\ExceptionController as BaseExceptionController;
use Oro\Component\Layout\LayoutContext;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Handles rendering error pages for front store.
 */
class ExceptionController extends BaseExceptionController
{
    const EXCEPTION_ROUTE_NAME = 'oro_frontend_exception';

    /** @var ContainerInterface */
    private $container;

    /** @var bool */
    private $showException;

    /** @var ExceptionValueMap */
    private $exceptionCodes;

    /**
     * @param ContainerInterface $container
     * @param bool $showException
     */
    public function __construct(ContainerInterface $container, $showException)
    {
        $this->container = $container;
        $this->showException = $showException;

        parent::__construct($container, $showException);
    }

    /**
     * {@inheritdoc}
     */
    public function showAction(Request $request, $exception, DebugLoggerInterface $logger = null)
    {
        if ($this->isLayoutRendering($request)) {
            $this->updateRequest($request);

            $code = $this->getStatusCodeFromThrowable($exception);
            $text = $this->getStatusText($code);

            $context = new LayoutContext(['data' => ['status_code' => $code , 'status_text' => $text]]);
            $context->set('route_name', self::EXCEPTION_ROUTE_NAME);

            try {
                $layout = $this->container->get(LayoutManager::class)
                    ->getLayout($context);

                return new Response($layout->render());
            } catch (\Throwable $e) {
                if (null !== $logger) {
                    $logger->error(
                        'Can\'t render layout template, because of errors in some layout templates',
                        ['exception'=> $e]
                    );
                }
            }
        }

        if ($exception instanceof \Throwable && !$exception instanceof \Exception) {
            $exception = new \Exception($exception->getMessage(), $exception->getCode(), $exception);
        }

        return parent::showAction($request, $exception, $logger);
    }

    /**
     * @param Request $request
     * @return bool
     */
    protected function isLayoutRendering(Request $request)
    {
        return
            $this->container->get(FrontendHelper::class)->isFrontendUrl($request->getPathInfo())
            && $request->getRequestFormat() === 'html'
            && !$this->showException($request)
            && !$this->isCircularHandlingException()
            && !$this->container->get(Mode::class)->isOn();
    }

    /**
     * @param Request $request
     * @return mixed
     */
    protected function showException(Request $request)
    {
        return $request->attributes->get('showException', $this->showException);
    }

    /**
     * @param int $code
     * @return string
     */
    protected function getStatusText($code)
    {
        return $this->container->get(TranslatorInterface::class)
            ->trans(sprintf('oro_frontend.exception.status_text.%d', $code));
    }

    private function isCircularHandlingException(): bool
    {
        $parentRequest = $this->getParentRequest();

        return $parentRequest && $parentRequest->get('_route') === self::EXCEPTION_ROUTE_NAME;
    }

    private function updateRequest(Request $request): void
    {
        $parentRequest = $this->getParentRequest();
        if (!$parentRequest) {
            return;
        }

        // emulate original request to render valid layout page
        $request->query->add($parentRequest->query->all());
        $request->request->add($parentRequest->request->all());
        $request->attributes->add($parentRequest->attributes->all());
        $request->cookies->add($parentRequest->cookies->all());
        $request->files->add($parentRequest->files->all());
        $request->server->add($parentRequest->server->all());
    }

    private function getParentRequest(): ?Request
    {
        return $this->container->get(RequestStack::class)->getParentRequest();
    }

    /**
     * Determines the status code to use for the response.
     */
    private function getStatusCodeFromThrowable(\Throwable $exception): int
    {
        // If matched
        if ($statusCode = $this->getExceptionCodes()->resolveThrowable($exception)) {
            return $statusCode;
        }

        // Otherwise, default
        if ($exception instanceof HttpExceptionInterface) {
            return $exception->getStatusCode();
        }

        return 500;
    }

    private function getExceptionCodes(): ExceptionValueMap
    {
        if (!$this->exceptionCodes) {
            $this->exceptionCodes = $this->container->get('fos_rest.exception.codes_map');
        }

        return $this->exceptionCodes;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices(): array
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                FrontendHelper::class,
                LayoutManager::class,
                RequestStack::class,
                TranslatorInterface::class,
                Mode::class
            ]
        );
    }
}
