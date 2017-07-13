<?php

namespace Oro\Bundle\CustomerBundle\Voter;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

use Oro\Bundle\FeatureToggleBundle\Checker\Voter\VoterInterface;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;

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

    /**
     * @param VoterInterface        $configVoter
     * @param TokenStorageInterface $tokenStorage
     */
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

    /**
     * {@inheritDoc}
     */
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
