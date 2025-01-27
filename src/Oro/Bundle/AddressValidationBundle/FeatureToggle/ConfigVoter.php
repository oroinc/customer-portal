<?php

namespace Oro\Bundle\AddressValidationBundle\FeatureToggle;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\FeatureToggleBundle\Checker\Voter\VoterInterface;
use Oro\Bundle\FeatureToggleBundle\Configuration\ConfigurationManager;
use Oro\Bundle\IntegrationBundle\Entity\Channel;

/**
 * Votes whether a feature ia available based on the state of configuration option associated with a feature.
 * Loads the {@see Channel} to ensure it belongs to the current organization. If not, treats the feature as disabled.
 */
class ConfigVoter implements VoterInterface
{
    public function __construct(
        private ConfigManager $configManager,
        private ConfigurationManager $featureConfigManager,
        private ManagerRegistry $doctrine
    ) {
    }

    #[\Override]
    public function vote($feature, $scopeIdentifier = null): int
    {
        if ($feature !== 'oro_address_validation') {
            return self::FEATURE_ABSTAIN;
        }

        $toggle = $this->featureConfigManager->get($feature, 'toggle');
        if (!$toggle) {
            return self::FEATURE_ABSTAIN;
        }

        $channelId = $this->configManager->get($toggle, false, false, $scopeIdentifier);
        if (!$channelId) {
            return self::FEATURE_DISABLED;
        }

        $channel = $this->doctrine->getRepository(Channel::class)->find($channelId);

        return $channel ? self::FEATURE_ENABLED : self::FEATURE_DISABLED;
    }
}
