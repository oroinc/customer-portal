<?php

namespace Oro\Bundle\CustomerBundle\Security;

use Oro\Bundle\CustomerBundle\Entity\CustomerVisitorManager;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\SecurityBundle\Authentication\Token\RolesAwareTokenInterface;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * The authentication provider for the storefront anonymous user.
 */
class AnonymousCustomerUserAuthenticationProvider implements AuthenticationProviderInterface
{
    /** @var CustomerVisitorManager */
    private $visitorManager;

    /** @var WebsiteManager */
    private $websiteManager;

    /** @var int */
    private $updateLatency;

    /**
     * @param CustomerVisitorManager $visitorManager
     * @param WebsiteManager         $websiteManager
     * @param integer                $updateLatency
     */
    public function __construct(
        CustomerVisitorManager $visitorManager,
        WebsiteManager $websiteManager,
        $updateLatency
    ) {
        $this->visitorManager = $visitorManager;
        $this->websiteManager = $websiteManager;
        $this->updateLatency = $updateLatency;
    }

    /**
     * {@inheritDoc}
     */
    public function supports(TokenInterface $token)
    {
        return $token instanceof AnonymousCustomerUserToken;
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

        $credentials = $token->getCredentials();
        $visitor = $this->visitorManager->findOrCreate($credentials['visitor_id'], $credentials['session_id']);
        $this->visitorManager->updateLastVisitTime($visitor, $this->updateLatency);

        return new AnonymousCustomerUserToken(
            $token->getUser(),
            $token instanceof RolesAwareTokenInterface ? $token->getRoles() : $token->getRoleNames(),
            $visitor,
            $organization
        );
    }
}
