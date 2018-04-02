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
    public function getLoginRoute(): ?string
    {
        $token = $this->tokenStorage->getToken();
        if ($token instanceof AnonymousCustomerUserToken || $token->getUser() instanceof CustomerUser) {
            return 'oro_customer_customer_user_security_login';
        }

        return $this->innerSecurityContext->getLoginRoute();
    }

    /**
     * {@inheritdoc}
     */
    public function getLogoutRoute(): ?string
    {
        $token = $this->tokenStorage->getToken();
        if ($token instanceof AnonymousCustomerUserToken || $token->getUser() instanceof CustomerUser) {
            return 'oro_customer_customer_user_security_logout';
        }

        return $this->innerSecurityContext->getLogoutRoute();
    }
}
