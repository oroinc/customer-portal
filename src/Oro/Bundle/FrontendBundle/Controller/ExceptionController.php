<?php

namespace Oro\Bundle\FrontendBundle\Controller;

use Oro\Bundle\UIBundle\Controller\ExceptionController as BaseExceptionController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;

class ExceptionController extends BaseExceptionController
{
    const EXCEPTION_ROUTE_NAME = 'oro_frontend_exception';

    /**
     * {@inheritdoc}
     */
    public function showAction(Request $request, $exception, DebugLoggerInterface $logger = null)
    {
        if ($this->isLayoutRendering($request)) {
            $container = $this->container;
            $code = $this->getStatusCode($exception);
            $text = $this->getStatusText($code);
            $url = $container->get('router')
                ->generate(self::EXCEPTION_ROUTE_NAME, ['code' => $code, 'text' => $text]);

            $subRequest = Request::create(
                $url,
                'GET',
                [],
                $request->cookies->all(),
                $request->files->all(),
                $request->server->all()
            );

            return $container->get('kernel')->handle($subRequest);
        }

        return parent::showAction($request, $exception, $logger);
    }

    /**
     * @param Request $request
     * @return bool
     */
    protected function isLayoutRendering(Request $request)
    {
        return $this->container->get('oro_frontend.request.frontend_helper')->isFrontendRequest($request)
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
        return $request->attributes->get('showException', $this->container->get('kernel')->isDebug());
    }

    /**
     * @param $code
     * @return string
     */
    protected function getStatusText($code)
    {
        return array_key_exists($code, Response::$statusTexts) ? Response::$statusTexts[$code] : "error";
    }

    /**
     * @return bool
     */
    private function isCircularHandlingException()
    {
        $requestStack = $this->container->get('request_stack');
        return $requestStack->getParentRequest()->get('_route') === self::EXCEPTION_ROUTE_NAME;
    }
}
