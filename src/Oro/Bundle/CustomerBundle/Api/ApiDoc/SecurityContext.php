<?php

namespace Oro\Bundle\CustomerBundle\Api\ApiDoc;

use Oro\Bundle\ApiBundle\ApiDoc\SecurityContextInterface;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;

/**
 * The implementation of the security context for the frontend API Sandbox.
 */
class SecurityContext implements SecurityContextInterface
{
    private SecurityContextInterface $innerSecurityContext;
    private FrontendHelper $frontendHelper;

    public function __construct(
        SecurityContextInterface $innerSecurityContext,
        FrontendHelper $frontendHelper
    ) {
        $this->innerSecurityContext = $innerSecurityContext;
        $this->frontendHelper = $frontendHelper;
    }

    /**
     * {@inheritDoc}
     */
    public function hasSecurityToken(): bool
    {
        return $this->innerSecurityContext->hasSecurityToken();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrganizations(): array
    {
        if ($this->frontendHelper->isFrontendRequest()) {
            return [];
        }

        return $this->innerSecurityContext->getOrganizations();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrganization(): ?string
    {
        if ($this->frontendHelper->isFrontendRequest()) {
            return null;
        }

        return $this->innerSecurityContext->getOrganization();
    }

    /**
     * {@inheritDoc}
     */
    public function getUserName(): ?string
    {
        return $this->innerSecurityContext->getUserName();
    }

    /**
     * {@inheritDoc}
     */
    public function getApiKey(): ?string
    {
        return $this->innerSecurityContext->getApiKey();
    }

    /**
     * {@inheritDoc}
     */
    public function getApiKeyGenerationHint(): ?string
    {
        if ($this->frontendHelper->isFrontendRequest()) {
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
     * {@inheritDoc}
     */
    public function getCsrfCookieName(): ?string
    {
        return $this->innerSecurityContext->getCsrfCookieName();
    }

    /**
     * {@inheritDoc}
     */
    public function getSwitchOrganizationRoute(): ?string
    {
        if ($this->frontendHelper->isFrontendRequest()) {
            return null;
        }

        return $this->innerSecurityContext->getSwitchOrganizationRoute();
    }

    /**
     * {@inheritDoc}
     */
    public function getLoginRoute(): ?string
    {
        if ($this->frontendHelper->isFrontendRequest()) {
            return 'oro_customer_customer_user_security_login';
        }

        return $this->innerSecurityContext->getLoginRoute();
    }

    /**
     * {@inheritDoc}
     */
    public function getLogoutRoute(): ?string
    {
        if ($this->frontendHelper->isFrontendRequest()) {
            return 'oro_customer_customer_user_security_logout';
        }

        return $this->innerSecurityContext->getLogoutRoute();
    }
}
