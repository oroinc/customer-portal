<?php

declare(strict_types=1);

namespace Oro\Bundle\AddressValidationBundle\Provider;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\AddressValidationBundle\DependencyInjection\Configuration;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Entity\Transport;

/**
 * Provides the {@see Transport} to use for the address validation.
 */
class AddressValidationTransportProvider
{
    public function __construct(
        private ManagerRegistry $doctrine,
        private ConfigManager $configManager
    ) {
    }

    public function getAddressValidationTransport(object|int|null $systemConfigScopeIdentifier = null): ?Transport
    {
        $channelId = $this->configManager->get(
            Configuration::getConfigKeyByName(Configuration::ADDRESS_VALIDATION_SERVICE),
            false,
            false,
            $systemConfigScopeIdentifier
        );

        if (!$channelId) {
            return null;
        }

        /** @var Channel|null $channel */
        $channel = $this->doctrine->getRepository(Channel::class)->find($channelId);

        return $channel?->getTransport();
    }
}
