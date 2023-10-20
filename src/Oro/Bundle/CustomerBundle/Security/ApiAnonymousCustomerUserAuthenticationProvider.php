<?php

namespace Oro\Bundle\CustomerBundle\Security;

use Oro\Bundle\CustomerBundle\Model\InMemoryCustomerVisitor;
use Oro\Bundle\CustomerBundle\Security\Token\ApiAnonymousCustomerUserToken;
use Oro\Bundle\SecurityBundle\Authentication\Token\RolesAwareTokenInterface;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * The authentication provider for the storefront API anonymous customer user that is not stored in the database.
 */
class ApiAnonymousCustomerUserAuthenticationProvider implements AuthenticationProviderInterface
{
    private WebsiteManager $websiteManager;

    public function __construct(WebsiteManager $websiteManager)
    {
        $this->websiteManager = $websiteManager;
    }

    /**
     * {@inheritDoc}
     */
    public function supports(TokenInterface $token)
    {
        return $token instanceof ApiAnonymousCustomerUserToken && !$token->getCredentials();
    }

    /**
     * {@inheritDoc}
     */
    public function authenticate(TokenInterface $token)
    {
        $website = $this->websiteManager->getCurrentWebsite();
        if (null === $website) {
            throw new AuthenticationException('The current website cannot be found.');
        }

        $organization = $website->getOrganization();
        if (null === $organization) {
            throw new AuthenticationException('The current website is not assigned to an organization.');
        }

        return new ApiAnonymousCustomerUserToken(
            $token->getUser(),
            $token instanceof RolesAwareTokenInterface ? $token->getRoles() : $token->getRoleNames(),
            new InMemoryCustomerVisitor(),
            $organization
        );
    }
}
