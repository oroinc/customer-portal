<?php

namespace Oro\Bundle\CustomerBundle\Api\ApiDoc;

use Oro\Bundle\ApiBundle\ApiDoc\SecurityContextInterface;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * The implementation of the security context for the frontend API Sandbox.
 */
class SecurityContext implements SecurityContextInterface
{
    /** @var SecurityContextInterface */
    private $innerSecurityContext;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /**
     * @param SecurityContextInterface $innerSecurityContext
     * @param TokenStorageInterface    $tokenStorage
     */
    public function __construct(
        SecurityContextInterface $innerSecurityContext,
        TokenStorageInterface $tokenStorage
    ) {
        $this->innerSecurityContext = $innerSecurityContext;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function hasSecurityToken(): bool
    {
        return $this->innerSecurityContext->hasSecurityToken();
    }

    /**
     * {@inheritdoc}
     */
    public function getUserName(): ?string
    {
        return $this->innerSecurityContext->getUserName();
    }

    /**
     * {@inheritdoc}
     */
    public function getApiKey(): ?string
    {
        return $this->innerSecurityContext->getApiKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getApiKeyGenerationHint(): ?string
    {
        if ($this->isFrontendApi()) {
            return
                'To use WSSE authentication the API key should be already generated'
                . ' for the current logged-in customer user.'
                . ' To generate it, execute POST request to "/api/login" API resource.'
                . ' If the "Enable API key generation" feature is enabled in the system configuration,'
                . ' the API key will be generated.'
                . ' After that reload this page.';
        }

        return $this->innerSecurityContext->getApiKeyGenerationHint();
    }

    /**
     * {@inheritdoc}
     */
    public function getCsrfCookieName(): ?string
    {
        return $this->innerSecurityContext->getCsrfCookieName();
    }

    /**
     * {@inheritdoc}
     */
    public function getLoginRoute(): ?string
    {
        if ($this->isFrontendApi()) {
            return 'oro_customer_customer_user_security_login';
        }

        return $this->innerSecurityContext->getLoginRoute();
    }

    /**
     * {@inheritdoc}
     */
    public function getLogoutRoute(): ?string
    {
        if ($this->isFrontendApi()) {
            return 'oro_customer_customer_user_security_logout';
        }

        return $this->innerSecurityContext->getLogoutRoute();
    }

    /**
     * @return bool
     */
    private function isFrontendApi(): bool
    {
        $token = $this->tokenStorage->getToken();

        return
            null !== $token
            && (
                $token instanceof AnonymousCustomerUserToken
                || $token->getUser() instanceof CustomerUser
            );
    }
}
