<?php

namespace Oro\Bundle\CustomerBundle\Security;

use Oro\Bundle\ApiBundle\Request\ApiRequestHelper;
use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;
use Oro\Bundle\CustomerBundle\Entity\CustomerVisitorManager;
use Oro\Bundle\CustomerBundle\Security\Badge\AnonymousCustomerUserBadge;
use Oro\Bundle\CustomerBundle\Security\Firewall\CustomerVisitorCookieFactory;
use Oro\Bundle\CustomerBundle\Security\Passport\AnonymousSelfValidatingPassport;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserTokenFactoryInterface;
use Oro\Bundle\SecurityBundle\Request\CsrfProtectedRequestHelper;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

/**
 * The authentication for the storefront anonymous user.
 */
class AnonymousCustomerUserAuthenticator implements AuthenticatorInterface
{
    public const COOKIE_ATTR_NAME = '_security_customer_visitor_cookie';
    public const COOKIE_NAME = 'customer_visitor';

    private ?TokenInterface $rememberedToken = null;

    public function __construct(
        private CustomerVisitorManager $visitorManager,
        private WebsiteManager $websiteManager,
        private TokenStorageInterface $tokenStorage,
        private AnonymousCustomerUserTokenFactoryInterface $anonymousTokenFactory,
        private CsrfProtectedRequestHelper $csrfProtectedRequestHelper,
        private CustomerVisitorCookieFactory $cookieFactory,
        private AnonymousCustomerUserRolesProvider $rolesProvider,
        private ApiRequestHelper $apiRequestHelper,
        private LoggerInterface $logger
    ) {
    }

    #[\Override]
    public function supports(Request $request): bool
    {
        $isAvailable = $this->shouldBeAuthenticatedAsCustomerVisitor($request);
        if (!$isAvailable) {
            return false;
        }
        $token = $this->tokenStorage->getToken();
        if (null === $token && null !== $this->rememberedToken) {
            $this->tokenStorage->setToken($this->rememberedToken);
            $this->rememberedToken = null;

            return false;
        }

        return true;
    }

    #[\Override]
    public function authenticate(Request $request): Passport
    {
        $website = $this->websiteManager->getCurrentWebsite();
        if (null === $website) {
            throw new AuthenticationException('The current website cannot be found.');
        }
        $organization = $website->getOrganization();
        if (null === $organization) {
            throw new AuthenticationException('The current website is not assigned to an organization.');
        }
        $credentials = (string)$request->cookies->get(self::COOKIE_NAME, '');
        $passport = new AnonymousSelfValidatingPassport(
            new AnonymousCustomerUserBadge($credentials, [$this, 'getVisitor']),
        );
        $passport->setAttribute('organization', $organization);
        $this->saveCredentials($request, $passport);

        return $passport;
    }

    #[\Override]
    public function createToken(Passport $passport, string $firewallName): TokenInterface
    {
        $token = $this->anonymousTokenFactory->create(
            $passport->getUser(),
            $passport->getAttribute('organization'),
            $this->rolesProvider->getRoles()
        );
        $this->tokenStorage->setToken($token);
        $this->logger->info('Populated the TokenStorage with an Anonymous Customer User Token.');
        /**
         * The token storage is always reset, we need to save our token to more permanent storage
         * to be possible to get it at the next execution of this authenticator.
         */
        $this->rememberedToken = $token;

        return $token;
    }

    #[\Override]
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    #[\Override]
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $this->logger->info('Customer User anonymous authentication failed.', ['exception' => $exception]);

        return null;
    }

    private function saveCredentials(Request $request, Passport $passport): void
    {
        $visitor = $passport->getUser();
        $request->attributes->set(
            self::COOKIE_ATTR_NAME,
            $this->cookieFactory->getCookie($visitor->getId(), $visitor->getSessionId())
        );
    }

    private function shouldBeAuthenticatedAsCustomerVisitor(Request $request): bool
    {
        $token = $this->tokenStorage->getToken();
        if (null === $token) {
            return !$this->apiRequestHelper->isApiRequest($request->getPathInfo())
                || $this->csrfProtectedRequestHelper->isCsrfProtectedRequest($request);
        }

        return $token instanceof AnonymousCustomerUserToken
            && $token->getVisitor() === null;
    }

    public function getVisitor(string $credentials): CustomerVisitor
    {
        $visitorId = $sessionId = null;

        if (!empty($credentials)) {
            $decodedCredentials = json_decode(base64_decode($credentials), false);
            if (JSON_ERROR_NONE === json_last_error() && null !== $decodedCredentials) {
                [$visitorId, $sessionId] =  $decodedCredentials;
            }
        }

        return $this->visitorManager->findOrCreate($visitorId, $sessionId);
    }
}
