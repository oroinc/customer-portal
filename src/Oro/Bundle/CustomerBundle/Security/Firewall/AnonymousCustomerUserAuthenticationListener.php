<?php

namespace Oro\Bundle\CustomerBundle\Security\Firewall;

use Doctrine\Common\Cache\CacheProvider;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\SecurityBundle\Csrf\CsrfRequestManager;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;

/**
 * This listener authenticates anonymous (AKA guest) customer users at frontend
 */
class AnonymousCustomerUserAuthenticationListener implements ListenerInterface
{
    const COOKIE_ATTR_NAME = '_security_customer_visitor_cookie';
    const COOKIE_NAME = 'customer_visitor';
    const CACHE_KEY = 'visitor_token';

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var AuthenticationManagerInterface
     */
    private $authenticationManager;

    /**
     * @var WebsiteManager
     */
    private $websiteManager;

    /**
     * This property is assumed to be filled on Request basis only so no need permanent cache for it
     * @var CacheProvider
     */
    private $cacheProvider;

    /** @var CsrfRequestManager */
    private $csrfRequestManager;

    /** @var string */
    private $apiPattern;

    /** @var CustomerVisitorCookieFactory */
    private $cookieFactory;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthenticationManagerInterface $authenticationManager,
        LoggerInterface $logger,
        WebsiteManager $websiteManager,
        CacheProvider $cacheProvider,
        CsrfRequestManager $csrfRequestManager,
        string $apiPattern,
        CustomerVisitorCookieFactory $cookieFactory
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authenticationManager = $authenticationManager;
        $this->logger = $logger;
        $this->websiteManager = $websiteManager;
        $this->cacheProvider = $cacheProvider;
        $this->csrfRequestManager = $csrfRequestManager;
        $this->apiPattern = $apiPattern;
        $this->cookieFactory = $cookieFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(GetResponseEvent $event)
    {
        $token = $this->tokenStorage->getToken();
        if (null === $token) {
            /**
             * Oro\Bundle\RedirectBundle\Security\Firewall two times triggers GetResponseEvent event
             * this causes current listener executes two times as well
             * So check if we already created and saved token for current request
             * If yes there is no need to do same actions once more
             */
            $cachedToken = $this->cacheProvider->fetch(self::CACHE_KEY);
            if ($cachedToken) {
                $this->tokenStorage->setToken($cachedToken);

                return;
            }
        }

        $request = $event->getRequest();
        if ($this->shouldBeAuthenticatedAsCustomerVisitor($request, $token)) {
            $token = new AnonymousCustomerUserToken(
                'Anonymous Customer User',
                $this->getRoles()
            );
            $token->setCredentials($this->getCredentials($request));

            try {
                $newToken = $this->authenticationManager->authenticate($token);

                $this->tokenStorage->setToken($newToken);
                $this->saveCredentials($request, $newToken);
                //Token storage is always reset so we need to save our token to more permanent property
                $this->cacheProvider->save(self::CACHE_KEY, $newToken);

                $this->logger->info('Populated the TokenStorage with an Anonymous Customer User Token.');
            } catch (AuthenticationException $e) {
                $this->logger->info('Customer User anonymous authentication failed.', ['exception' => $e]);
            }
        }
    }

    /**
     * @return array
     */
    private function getRoles()
    {
        $currentWebsite = $this->websiteManager->getCurrentWebsite();
        if (!$currentWebsite || !$currentWebsite->getGuestRole() || !$currentWebsite->getGuestRole()->getRole()) {
            return [];
        }
        /** @var CustomerUserRole $guestRole */
        $guestRole = $currentWebsite->getGuestRole();
        return [$guestRole->getRole()];
    }

    /**
     * @param Request $request
     * @return array
     */
    private function getCredentials(Request $request)
    {
        $value = $request->cookies->get(self::COOKIE_NAME);
        if ($value) {
            list($visitorId, $sessionId) = json_decode(base64_decode($value));
        } else {
            $visitorId = null;
            $sessionId = null;
        }

        return [
            'visitor_id' => $visitorId,
            'session_id' => $sessionId,
        ];
    }

    private function saveCredentials(Request $request, AnonymousCustomerUserToken $token)
    {
        $visitor = $token->getVisitor();
        if (!$visitor) {
            return;
        }

        $request->attributes->set(
            self::COOKIE_ATTR_NAME,
            $this->cookieFactory->getCookie($visitor->getId(), $visitor->getSessionId())
        );
    }

    private function shouldBeAuthenticatedAsCustomerVisitor(Request $request, TokenInterface $token = null): bool
    {
        if (null === $token) {
            return
                !$this->isApiRequest($request)
                || $this->isAjaxApiRequest($request);
        }

        return
            $token instanceof AnonymousCustomerUserToken
            && $token->getVisitor() === null;
    }

    private function isApiRequest(Request $request): bool
    {
        return preg_match('{' . $this->apiPattern . '}', $request->getPathInfo()) === 1;
    }

    /**
     * Checks whether the request is AJAX request to API resource
     * (cookies has the session cookie and the request has "X-CSRF-Header" header with valid CSRF token).
     */
    private function isAjaxApiRequest(Request $request): bool
    {
        $isGetRequest = $request->isMethod('GET');

        return
            null !== $request->getSession()
            && $request->cookies->has($request->getSession()->getName())
            && (
                (!$isGetRequest && $this->csrfRequestManager->isRequestTokenValid($request))
                || ($isGetRequest && $request->headers->has(CsrfRequestManager::CSRF_HEADER))
            );
    }
}
