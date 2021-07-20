<?php

namespace Oro\Bundle\CustomerBundle\Voter;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\FeatureToggleBundle\Checker\Voter\VoterInterface;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;

class CustomerUserVoter implements VoterInterface
{
    /**
     * @var TokenAccessorInterface
     */
    private $tokenAccessor;

    /**
     * @var string
     */
    private $featureName;

    public function __construct(TokenAccessorInterface $tokenAccessor)
    {
        $this->tokenAccessor = $tokenAccessor;
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
            if ($this->tokenAccessor->getUser() instanceof CustomerUser) {
                return VoterInterface::FEATURE_ENABLED;
            }
        }

        return VoterInterface::FEATURE_ABSTAIN;
    }
}
