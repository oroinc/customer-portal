<?php

namespace Oro\Bundle\FrontendBundle\EventListener;

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
    /** @var ContainerInterface */
    private $container;

    /** @var string */
    private $apiPattern;

    /** @var string */
    private $backendPrefix;

    public function __construct(ContainerInterface $container, string $apiPattern, string $backendPrefix)
    {
        $this->container = $container;
        $this->apiPattern = $apiPattern;
        $this->backendPrefix = $backendPrefix;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices(): array
    {
        return [
            'handler'          => RequestActionHandler::class,
            'frontend_handler' => RequestActionHandler::class,
            FrontendHelper::class
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();
        $pathInfo = $request->getPathInfo();
        /** @var FrontendHelper $frontendHelper */
        $frontendHelper = $this->container->get(FrontendHelper::class);
        $isFrontendRequest = $frontendHelper->isFrontendUrl($pathInfo);
        if (!$isFrontendRequest) {
            $pathInfo = substr($pathInfo, \strlen($this->backendPrefix));
        }
        if (!$this->isApiRequest($pathInfo)) {
            return;
        }

        /** @var RequestActionHandler $actionHandler */
        $actionHandler = $isFrontendRequest
            ? $this->container->get('frontend_handler')
            : $this->container->get('handler');
        $event->setResponse($actionHandler->handleUnhandledError($request, $event->getThrowable()));
    }

    private function isApiRequest(string $pathInfo): bool
    {
        return preg_match('{' . $this->apiPattern . '}', $pathInfo) === 1;
    }
}
