<?php

namespace Oro\Bundle\CustomerBundle\Security;

use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

use Oro\Bundle\CustomerBundle\Entity\CustomerVisitorManager;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;

class AnonymousCustomerUserAuthenticationProvider implements AuthenticationProviderInterface
{
    /**
     * @var CustomerVisitorManager
     */
    private $visitorManager;

    /**
     * @var integer
     */
    private $updateLatency;

    /**
     * @param CustomerVisitorManager $visitorManager
     * @param integer                $updateLatency
     */
    public function __construct(
        CustomerVisitorManager $visitorManager,
        $updateLatency
    ) {
        $this->visitorManager = $visitorManager;
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

        return new AnonymousCustomerUserToken(
            $token->getUser(),
            $token->getRoles(),
            $visitor
        );
    }
}
