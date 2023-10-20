<?php

namespace Oro\Bundle\CustomerBundle\Security\Firewall;

use Oro\Bundle\CustomerBundle\Security\AnonymousCustomerUserRolesProvider;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\SecurityBundle\Csrf\CsrfRequestManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * Authenticates anonymous customer users at the storefront.
 */
class AnonymousCustomerUserAuthenticationListener
{
    public const COOKIE_ATTR_NAME = '_security_customer_visitor_cookie';
    public const COOKIE_NAME = 'customer_visitor';
    public const CACHE_KEY = 'visitor_token';

    private TokenStorageInterface $tokenStorage;
    private AuthenticationManagerInterface $authenticationManager;
    private CsrfRequestManager $csrfRequestManager;
    private CustomerVisitorCookieFactory $cookieFactory;
    private AnonymousCustomerUserRolesProvider $rolesProvider;
    private string $apiPattern;
    private LoggerInterface $logger;
    private ?TokenInterface $rememberedToken = null;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthenticationManagerInterface $authenticationManager,
        CsrfRequestManager $csrfRequestManager,
        CustomerVisitorCookieFactory $cookieFactory,
        AnonymousCustomerUserRolesProvider $rolesProvider,
        string $apiPattern,
        LoggerInterface $logger,
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authenticationManager = $authenticationManager;
        $this->csrfRequestManager = $csrfRequestManager;
        $this->cookieFactory = $cookieFactory;
        $this->rolesProvider = $rolesProvider;
        $this->apiPattern = $apiPattern;
        $this->logger = $logger;
    }

    public function __invoke(RequestEvent $event): void
    {
        $token = $this->tokenStorage->getToken();

        /**
         * {@see \Oro\Bundle\RedirectBundle\Security\Firewall} triggers RequestEvent event two times.
         * This causes this listener executes two times as well.
         * So, check if we already created and saved token for the current request.
         * If yes, there is no need to do same actions once more.
         */
        if (null === $token && null !== $this->rememberedToken) {
            $this->tokenStorage->setToken($this->rememberedToken);
            $this->rememberedToken = null;

            return;
        }

        $request = $event->getRequest();
        if ($this->shouldBeAuthenticatedAsCustomerVisitor($request, $token)) {
            $token = new AnonymousCustomerUserToken('Anonymous Customer User', $this->rolesProvider->getRoles());
            $token->setCredentials($this->getCredentials($request));

            $authenticatedToken = $this->authenticate($token);
            if (null !== $authenticatedToken) {
                $this->tokenStorage->setToken($authenticatedToken);
                $this->saveCredentials($request, $authenticatedToken);
                $this->logger->info('Populated the TokenStorage with an Anonymous Customer User Token.');

                /**
                 * The token storage is always reset, we need to save our token to more permanent storage
                 * to be possible to get it at the next execution of this listener.
                 */
                $this->rememberedToken = $authenticatedToken;
            }
        }
    }

    private function authenticate(AnonymousCustomerUserToken $token): ?AnonymousCustomerUserToken
    {
        try {
            /** @noinspection PhpIncompatibleReturnTypeInspection */
            return $this->authenticationManager->authenticate($token);
        } catch (AuthenticationException $e) {
            $this->logger->info('Customer User anonymous authentication failed.', ['exception' => $e]);

            return null;
        }
    }

    private function getCredentials(Request $request): array
    {
        $value = $request->cookies->get(self::COOKIE_NAME);
        if ($value) {
            [$visitorId, $sessionId] = json_decode(base64_decode($value), false, 512, JSON_THROW_ON_ERROR);
        } else {
            $visitorId = null;
            $sessionId = null;
        }

        return [
            'visitor_id' => $visitorId,
            'session_id' => $sessionId,
        ];
    }

    private function saveCredentials(Request $request, AnonymousCustomerUserToken $token): void
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
            $request->hasSession()
            && $request->cookies->has($request->getSession()->getName())
            && (
                (!$isGetRequest && $this->csrfRequestManager->isRequestTokenValid($request))
                || ($isGetRequest && $request->headers->has(CsrfRequestManager::CSRF_HEADER))
            );
    }
}
