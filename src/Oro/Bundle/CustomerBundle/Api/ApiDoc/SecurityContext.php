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

    #[\Override]
    public function hasSecurityToken(): bool
    {
        return $this->innerSecurityContext->hasSecurityToken();
    }

    #[\Override]
    public function getOrganizations(): array
    {
        if ($this->frontendHelper->isFrontendRequest()) {
            return [];
        }

        return $this->innerSecurityContext->getOrganizations();
    }

    #[\Override]
    public function getOrganization(): ?string
    {
        if ($this->frontendHelper->isFrontendRequest()) {
            return null;
        }

        return $this->innerSecurityContext->getOrganization();
    }

    #[\Override]
    public function getUserName(): ?string
    {
        return $this->innerSecurityContext->getUserName();
    }

    #[\Override]
    public function getCsrfCookieName(): ?string
    {
        return $this->innerSecurityContext->getCsrfCookieName();
    }

    #[\Override]
    public function getSwitchOrganizationRoute(): ?string
    {
        if ($this->frontendHelper->isFrontendRequest()) {
            return null;
        }

        return $this->innerSecurityContext->getSwitchOrganizationRoute();
    }

    #[\Override]
    public function getLoginRoute(): ?string
    {
        if ($this->frontendHelper->isFrontendRequest()) {
            return 'oro_customer_customer_user_security_login';
        }

        return $this->innerSecurityContext->getLoginRoute();
    }

    #[\Override]
    public function getLogoutRoute(): ?string
    {
        if ($this->frontendHelper->isFrontendRequest()) {
            return 'oro_customer_customer_user_security_logout';
        }

        return $this->innerSecurityContext->getLogoutRoute();
    }
}
