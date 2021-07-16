<?php

namespace Oro\Bundle\FrontendBundle\EventListener;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\FrontendBundle\GuestAccess\GuestAccessDecisionMakerInterface;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\RouterInterface;

/**
 * Checks guest access.
 * If access is denied and was triggered regular request - returns redirect response to login page.
 * If access is denied and was triggered API request - returns 401 response.
 *
 * This listener should be triggered only after firewall and authentication are passed, routing and slugs are resolved
 * in Oro\Bundle\RedirectBundle\Security\Firewall (security.firewall service). Otherwise it'll become bug prone due to:
 *  - Prematurely initialized user configuration scope (user is not authenticated yet, but ConfigManager is called)
 *  - Prematurely allowance for the URL that might become closed after slugs are resolved
 *
 * Hence, it should be triggered after the priority 8. In order to give others ability to change the behavior, it was
 * decided to put this listener on priority 5.
 */
class GuestAccessRequestListener
{
    private TokenAccessorInterface $tokenAccessor;

    private ConfigManager $configManager;

    private GuestAccessDecisionMakerInterface $guestAccessDecisionMaker;

    private RouterInterface $router;

    private string $restApiPrefix;

    public function __construct(
        TokenAccessorInterface $tokenAccessor,
        ConfigManager $configManager,
        GuestAccessDecisionMakerInterface $guestAccessDeniedDecisionMaker,
        RouterInterface $router,
        string $restApiPrefix
    ) {
        $this->tokenAccessor = $tokenAccessor;
        $this->configManager = $configManager;
        $this->guestAccessDecisionMaker = $guestAccessDeniedDecisionMaker;
        $this->router = $router;
        $this->restApiPrefix = $restApiPrefix;
    }

    /**
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        if ($this->tokenAccessor->hasUser()) {
            return;
        }

        if ($this->configManager->get('oro_frontend.guest_access_enabled')) {
            return;
        }

        $requestPathInfo = $event->getRequest()->getPathInfo();
        $decision = $this->guestAccessDecisionMaker->decide($requestPathInfo);
        if ($decision === GuestAccessDecisionMakerInterface::URL_DISALLOW
            && $event->getRequest()->getMethod() !== Request::METHOD_OPTIONS
        ) {
            $response = $this->createResponse(
                $this->getCustomerUserLoginUrl(),
                $this->isApiRequest($requestPathInfo) ? Response::HTTP_UNAUTHORIZED : Response::HTTP_FOUND
            );
            $event->setResponse($response);
        }
    }

    private function createResponse(string $url, int $status = Response::HTTP_FOUND): RedirectResponse|Response
    {
        return Response::HTTP_UNAUTHORIZED === $status
            ? new Response('', $status)
            : new RedirectResponse($url, $status);
    }

    private function getCustomerUserLoginUrl(): string
    {
        return $this->router->generate('oro_customer_customer_user_security_login');
    }

    private function isApiRequest(string $pathInfo): bool
    {
        return str_starts_with($pathInfo, $this->restApiPrefix);
    }
}
