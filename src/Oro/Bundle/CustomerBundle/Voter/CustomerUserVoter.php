<?php

namespace Oro\Bundle\CustomerBundle\Voter;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\FeatureToggleBundle\Checker\Voter\VoterInterface;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;

/**
 * Voter for feature toggle decisions based on customer user authentication.
 *
 * This voter enables a specific feature when the current user is authenticated as a
 * customer user. For non-customer users, it abstains from voting, allowing other voters
 * to make the feature toggle decision.
 */
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

    #[\Override]
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
