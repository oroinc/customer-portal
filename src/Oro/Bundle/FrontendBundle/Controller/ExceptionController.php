<?php

namespace Oro\Bundle\FrontendBundle\Controller;

use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\LayoutBundle\Layout\LayoutManager;
use Oro\Bundle\UIBundle\Controller\ExceptionController as BaseExceptionController;
use Oro\Component\Layout\LayoutContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Handles rendering error pages.
 */
class ExceptionController extends BaseExceptionController
{
    const EXCEPTION_ROUTE_NAME = 'oro_frontend_exception';

    /** @var FrontendHelper */
    private $frontendHelper;

    /** @var TranslatorInterface */
    private $translator;

    /** @var RequestStack */
    private $requestStack;

    /** @var LayoutManager */
    private $layout;

    /** @var Kernel */
    private $kernel;

    /**
     * @param FrontendHelper $frontendHelper
     * @param TranslatorInterface $translator
     * @param RequestStack $requestStack
     * @param LayoutManager $layout
     * @param Kernel $kernel
     */
    public function __construct(
        FrontendHelper $frontendHelper,
        TranslatorInterface $translator,
        RequestStack $requestStack,
        LayoutManager $layout,
        Kernel $kernel
    ) {
        $this->frontendHelper = $frontendHelper;
        $this->translator = $translator;
        $this->requestStack = $requestStack;
        $this->layout = $layout;
        $this->kernel = $kernel;
    }

    /**
     * {@inheritdoc}
     */
    public function showAction(Request $request, $exception, DebugLoggerInterface $logger = null)
    {
        if ($this->isLayoutRendering($request)) {
            $this->updateRequest($request);

            $code = $this->getStatusCode($exception);
            $text = $this->getStatusText($code);

            $context = new LayoutContext(['data' => ['status_code' => $code , 'status_text' => $text]]);
            $context->set('route_name', self::EXCEPTION_ROUTE_NAME);

            $layout = $this->layout
                ->getLayout($context);

            return new Response($layout->render());
        }

        return parent::showAction($request, $exception, $logger);
    }

    /**
     * @param Request $request
     * @return bool
     */
    protected function isLayoutRendering(Request $request)
    {
        return $this->frontendHelper->isFrontendRequest($request)
            && $request->getRequestFormat() === 'html'
            && !$this->showException($request)
            && !$this->isCircularHandlingException();
    }

    /**
     * @param Request $request
     * @return mixed
     */
    protected function showException(Request $request)
    {
        return $request->attributes->get('showException', $this->kernel->isDebug());
    }

    /**
     * @param int $code
     * @return string
     */
    protected function getStatusText($code)
    {
        return $this->translator->trans(sprintf('oro_frontend.exception.status_text.%d', $code));
    }

    /**
     * @return bool
     */
    private function isCircularHandlingException(): bool
    {
        $parentRequest = $this->getParentRequest();

        return $parentRequest && $parentRequest->get('_route') === self::EXCEPTION_ROUTE_NAME;
    }

    /**
     * @param Request $request
     */
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

    /**
     * @return Request|null
     */
    private function getParentRequest(): ?Request
    {
        return $this->requestStack->getParentRequest();
    }
}
