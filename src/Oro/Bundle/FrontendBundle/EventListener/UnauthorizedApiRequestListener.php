<?php

namespace Oro\Bundle\FrontendBundle\EventListener;

use Oro\Bundle\ApiBundle\Request\ApiRequestHelper;
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

    public function onKernelResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        if (Response::HTTP_UNAUTHORIZED !== $response->getStatusCode()) {
            return;
        }

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
            $this->getActionHandler($isFrontendRequest)->handleUnhandledError(
                $request,
                $this->createUnauthorizedHttpException($response)
            )
        );
    }

    private function getActionHandler(bool $isFrontendRequest): RequestActionHandler
    {
        return $isFrontendRequest
            ? $this->container->get('frontend_handler')
            : $this->container->get('handler');
    }

    private function createUnauthorizedHttpException(Response $response): HttpException
    {
        $headers = [];
        if ($response->headers->has(self::WWW_AUTHENTICATE_HEADER)) {
            $headers[self::WWW_AUTHENTICATE_HEADER] = $response->headers->get(self::WWW_AUTHENTICATE_HEADER);
        }

        return new HttpException(Response::HTTP_UNAUTHORIZED, $response->getContent() ?: '', null, $headers, 0);
    }
}
