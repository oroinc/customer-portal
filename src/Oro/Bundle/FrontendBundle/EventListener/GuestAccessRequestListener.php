<?php

namespace Oro\Bundle\FrontendBundle\EventListener;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\FrontendBundle\GuestAccess\GuestAccessDecisionMakerInterface;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
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
    /**
     * @var TokenAccessorInterface
     */
    private $tokenAccessor;

    /**
     * @var ConfigManager
     */
    private $configManager;

    /**
     * @var GuestAccessDecisionMakerInterface
     */
    private $guestAccessDecisionMaker;
    /**
     * @var RouterInterface
     */
    private $router;

    /** @var string */
    private $restApiPrefix;

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
    public function onKernelRequest(GetResponseEvent $event)
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

    /**
     * @param string $url
     * @param int    $status
     *
     * @return RedirectResponse|Response
     */
    private function createResponse(string $url, $status = Response::HTTP_FOUND)
    {
        return Response::HTTP_UNAUTHORIZED === $status
            ? new Response('', $status)
            : new RedirectResponse($url, $status);
    }

    /**
     * @return string
     */
    private function getCustomerUserLoginUrl()
    {
        return $this->router->generate('oro_customer_customer_user_security_login');
    }

    /**
     * @param string $pathInfo
     *
     * @return bool
     */
    private function isApiRequest(string $pathInfo)
    {
        return 0 === strpos($pathInfo, $this->restApiPrefix);
    }
}
