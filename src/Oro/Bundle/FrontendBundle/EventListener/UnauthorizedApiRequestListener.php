<?php

namespace Oro\Bundle\FrontendBundle\EventListener;

use Oro\Bundle\ApiBundle\Request\Rest\RequestActionHandler;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

/**
 * Builds a response for an unauthorized API requests.
 */
class UnauthorizedApiRequestListener implements ServiceSubscriberInterface
{
    private const WWW_AUTHENTICATE_HEADER = 'WWW-Authenticate';

    private ContainerInterface $container;
    private string $apiPattern;
    private string $backendPrefix;

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

    public function onKernelResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        if (Response::HTTP_UNAUTHORIZED !== $response->getStatusCode()) {
            return;
        }

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
        $event->setResponse(
            $actionHandler->handleUnhandledError(
                $request,
                $this->createUnauthorizedHttpException($response)
            )
        );
    }

    private function createUnauthorizedHttpException(Response $response): HttpException
    {
        $headers = [];
        if ($response->headers->has(self::WWW_AUTHENTICATE_HEADER)) {
            $headers[self::WWW_AUTHENTICATE_HEADER] = $response->headers->get(self::WWW_AUTHENTICATE_HEADER);
        }

        return new HttpException(Response::HTTP_UNAUTHORIZED, $response->getContent() ?: '', null, $headers, 0);
    }

    private function isApiRequest(string $pathInfo): bool
    {
        return preg_match('{' . $this->apiPattern . '}', $pathInfo) === 1;
    }
}
