<?php

namespace Oro\Bundle\CustomerBundle\Security;

use Oro\Bundle\CustomerBundle\Entity\CustomerVisitorManager;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AnonymousCustomerUserAuthenticationProvider implements AuthenticationProviderInterface
{
    /**
     * @var CustomerVisitorManager
     */
    private $visitorManager;

    /**
     * @var WebsiteManager
     */
    private $websiteManager;

    /**
     * @var integer
     */
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
        if (!$this->supports($token)) {
            return null;
        }

        $credentials = $token->getCredentials();
        $visitor = $this->visitorManager->findOrCreate($credentials['visitor_id'], $credentials['session_id']);

        $this->visitorManager->updateLastVisitTime($visitor, $this->updateLatency);

        $organization = null;
        $website = $this->websiteManager->getCurrentWebsite();
        if ($website !== null) {
            $organization = $website->getOrganization();
        }

        return new AnonymousCustomerUserToken(
            $token->getUser(),
            $token->getRoles(),
            $visitor,
            $organization
        );
    }
}
