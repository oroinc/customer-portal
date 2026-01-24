<?php

namespace Oro\Bundle\CustomerBundle\Voter;

use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\FeatureToggleBundle\Checker\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Voter for feature toggle decisions based on anonymous customer user authentication.
 *
 * This voter delegates feature toggle decisions to a configuration voter when the current
 * user is authenticated as an anonymous customer user (guest visitor). For other authentication
 * contexts, it abstains from voting, allowing other voters to make the decision.
 */
class AnonymousCustomerUserVoter implements VoterInterface
{
    /**
     * @var VoterInterface
     */
    private $configVoter;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var string
     */
    private $featureName;

    public function __construct(VoterInterface $configVoter, TokenStorageInterface $tokenStorage)
    {
        $this->configVoter  = $configVoter;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param string $featureName
     */
    public function setFeatureName($featureName)
    {
        $this->featureName = $featureName;
    }

    #[\Override]
    public function vote($feature, $scopeIdentifier = null)
    {
        if ($feature === $this->featureName) {
            if ($this->tokenStorage->getToken() instanceof AnonymousCustomerUserToken) {
                return $this->configVoter->vote($feature, $scopeIdentifier);
            }
        }

        return VoterInterface::FEATURE_ABSTAIN;
    }
}
