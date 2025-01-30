<?php

namespace Oro\Bundle\CustomerBundle\Security;

use Oro\Bundle\ApiBundle\Request\ApiRequestHelper;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\Model\InMemoryCustomerVisitor;
use Oro\Bundle\CustomerBundle\Security\Badge\AnonymousCustomerUserBadge;
use Oro\Bundle\CustomerBundle\Security\Firewall\ApiAnonymousCustomerUserAuthenticationDecisionMaker;
use Oro\Bundle\CustomerBundle\Security\Passport\AnonymousSelfValidatingPassport;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserTokenFactoryInterface;
use Oro\Bundle\CustomerBundle\Security\Token\ApiAnonymousCustomerUserToken;
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
 * The authentication provider for the storefront API anonymous customer user that is not stored in the database.
 */
class ApiAnonymousCustomerUserAuthenticator implements AuthenticatorInterface
{
    private ?TokenInterface $rememberedToken = null;

    public function __construct(
        private WebsiteManager $websiteManager,
        private TokenStorageInterface $tokenStorage,
        private AnonymousCustomerUserTokenFactoryInterface $anonymousTokenFactory,
        private AnonymousCustomerUserRolesProvider $rolesProvider,
        private ApiRequestHelper $apiRequestHelper,
        private ConfigManager $configManager,
        private ApiAnonymousCustomerUserAuthenticationDecisionMaker $decisionMaker,
        private LoggerInterface $logger
    ) {
    }

    #[\Override]
    public function supports(Request $request): bool
    {
        $isAvailable = $this->shouldBeAuthenticatedAsCustomerVisitor($request, $this->tokenStorage->getToken())
            && $this->decisionMaker->isAnonymousCustomerUserAllowed($request);

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
        $passport = new AnonymousSelfValidatingPassport(
            new AnonymousCustomerUserBadge('', fn () => new InMemoryCustomerVisitor()),
        );
        $passport->setAttribute('organization', $organization);

        return $passport;
    }

    #[\Override]
    public function createToken(Passport $passport, string $firewallName): TokenInterface
    {
        $token = $this->anonymousTokenFactory->createApi(
            $passport->getUser(),
            $passport->getAttribute('organization'),
            $this->rolesProvider->getRoles()
        );
        $this->tokenStorage->setToken($token);
        $this->logger->info('Populated the TokenStorage with an API Anonymous Customer User Token.');
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
        $this->logger->info('API Customer User anonymous authentication failed.', ['exception' => $exception]);

        return null;
    }

    private function shouldBeAuthenticatedAsCustomerVisitor(Request $request, ?TokenInterface $token = null): bool
    {
        if (null === $token) {
            return $this->apiRequestHelper->isApiRequest($request->getPathInfo())
                && $this->configManager->get('oro_customer.non_authenticated_visitors_api');
        }

        return $token instanceof ApiAnonymousCustomerUserToken
            && $token->getVisitor() === null;
    }
}
