<?php

namespace Oro\Bundle\CustomerBundle\Security\Firewall;

use Oro\Bundle\ApiBundle\Request\ApiRequestHelper;
use Oro\Bundle\CustomerBundle\Security\AnonymousCustomerUserRolesProvider;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\SecurityBundle\Request\CsrfProtectedRequestHelper;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * This listener authenticates anonymous (AKA guest) customer users at frontend
 */
class AnonymousCustomerUserAuthenticationListener
{
    public const COOKIE_ATTR_NAME = '_security_customer_visitor_cookie';
    public const COOKIE_NAME = 'customer_visitor';
    public const CACHE_KEY = 'visitor_token';

    private TokenStorageInterface $tokenStorage;
    private AuthenticationManagerInterface $authenticationManager;
    private CsrfProtectedRequestHelper $csrfProtectedRequestHelper;
    private CustomerVisitorCookieFactory $cookieFactory;
    private AnonymousCustomerUserRolesProvider $rolesProvider;
    private ApiRequestHelper $apiRequestHelper;
    private LoggerInterface $logger;
    private ?TokenInterface $rememberedToken = null;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthenticationManagerInterface $authenticationManager,
        CsrfProtectedRequestHelper $csrfProtectedRequestHelper,
        CustomerVisitorCookieFactory $cookieFactory,
        AnonymousCustomerUserRolesProvider $rolesProvider,
        ApiRequestHelper $apiRequestHelper,
        LoggerInterface $logger,
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authenticationManager = $authenticationManager;
        $this->csrfProtectedRequestHelper = $csrfProtectedRequestHelper;
        $this->cookieFactory = $cookieFactory;
        $this->rolesProvider = $rolesProvider;
        $this->apiRequestHelper = $apiRequestHelper;
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
                !$this->apiRequestHelper->isApiRequest($request->getPathInfo())
                || $this->csrfProtectedRequestHelper->isCsrfProtectedRequest($request);
        }

        return
            $token instanceof AnonymousCustomerUserToken
            && $token->getVisitor() === null;
    }
}
