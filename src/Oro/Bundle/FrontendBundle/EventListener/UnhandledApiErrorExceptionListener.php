<?php

namespace Oro\Bundle\FrontendBundle\EventListener;

use Oro\Bundle\ApiBundle\Request\ApiRequestHelper;
use Oro\Bundle\ApiBundle\Request\Rest\RequestActionHandler;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

/**
 * Builds a response for case when an unexpected error happens before any public API action is started.
 */
class UnhandledApiErrorExceptionListener implements ServiceSubscriberInterface
{
    private ContainerInterface $container;
    private ApiRequestHelper $apiRequestHelper;
    private FrontendHelper $frontendHelper;
    private string $backendPrefix;

    public function __construct(
        ContainerInterface $container,
        ApiRequestHelper $apiRequestHelper,
        FrontendHelper $frontendHelper,
        string $backendPrefix
    ) {
        $this->container = $container;
        $this->apiRequestHelper = $apiRequestHelper;
        $this->frontendHelper = $frontendHelper;
        $this->backendPrefix = $backendPrefix;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices(): array
    {
        return [
            'handler'          => RequestActionHandler::class,
            'frontend_handler' => RequestActionHandler::class
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();
        $pathInfo = $request->getPathInfo();
        $isFrontendRequest = $this->frontendHelper->isFrontendUrl($pathInfo);
        if (!$isFrontendRequest) {
            $pathInfo = substr($pathInfo, \strlen($this->backendPrefix));
        }
        if (!$this->apiRequestHelper->isApiRequest($pathInfo)) {
            return;
        }

        $event->setResponse(
            $this->getActionHandler($isFrontendRequest)->handleUnhandledError($request, $event->getThrowable())
        );
    }

    private function getActionHandler(bool $isFrontendRequest): RequestActionHandler
    {
        return $isFrontendRequest
            ? $this->container->get('frontend_handler')
            : $this->container->get('handler');
    }
}
