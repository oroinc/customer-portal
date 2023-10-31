<?php

namespace Oro\Bundle\CustomerBundle\Security\Firewall;

use Oro\Bundle\ApiBundle\Request\ApiRequestHelper;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\Security\AnonymousCustomerUserRolesProvider;
use Oro\Bundle\CustomerBundle\Security\Token\ApiAnonymousCustomerUserToken;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * Authenticates anonymous customer users that are not stored in the database at the storefront.
 */
class ApiAnonymousCustomerUserAuthenticationListener
{
    private TokenStorageInterface $tokenStorage;
    private AuthenticationManagerInterface $authenticationManager;
    private AnonymousCustomerUserRolesProvider $rolesProvider;
    private ApiRequestHelper $apiRequestHelper;
    private ConfigManager $configManager;
    private ApiAnonymousCustomerUserAuthenticationDecisionMaker $decisionMaker;
    private LoggerInterface $logger;
    private ?TokenInterface $rememberedToken = null;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthenticationManagerInterface $authenticationManager,
        AnonymousCustomerUserRolesProvider $rolesProvider,
        ApiRequestHelper $apiRequestHelper,
        ConfigManager $configManager,
        ApiAnonymousCustomerUserAuthenticationDecisionMaker $decisionMaker,
        LoggerInterface $logger,
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authenticationManager = $authenticationManager;
        $this->rolesProvider = $rolesProvider;
        $this->apiRequestHelper = $apiRequestHelper;
        $this->configManager = $configManager;
        $this->decisionMaker = $decisionMaker;
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
        if ($this->shouldBeAuthenticatedAsApiCustomerVisitor($request, $token)
            && $this->decisionMaker->isAnonymousCustomerUserAllowed($request)
        ) {
            $token = new ApiAnonymousCustomerUserToken(
                'API Anonymous Customer User',
                $this->rolesProvider->getRoles()
            );

            $authenticatedToken = $this->authenticate($token);
            if (null !== $authenticatedToken) {
                $this->tokenStorage->setToken($authenticatedToken);
                $this->logger->info('Populated the TokenStorage with an API Anonymous Customer User Token.');

                /**
                 * The token storage is always reset, we need to save our token to more permanent storage
                 * to be possible to get it at the next execution of this listener.
                 */
                $this->rememberedToken = $authenticatedToken;
            }
        }
    }

    private function authenticate(ApiAnonymousCustomerUserToken $token): ?ApiAnonymousCustomerUserToken
    {
        try {
            /** @noinspection PhpIncompatibleReturnTypeInspection */
            return $this->authenticationManager->authenticate($token);
        } catch (AuthenticationException $e) {
            $this->logger->info('API Customer User anonymous authentication failed.', ['exception' => $e]);

            return null;
        }
    }

    private function shouldBeAuthenticatedAsApiCustomerVisitor(Request $request, ?TokenInterface $token): bool
    {
        if (null === $token) {
            return
                $this->apiRequestHelper->isApiRequest($request->getPathInfo())
                && $this->configManager->get('oro_customer.non_authenticated_visitors_api');
        }

        return
            $token instanceof ApiAnonymousCustomerUserToken
            && $token->getVisitor() === null;
    }
}
