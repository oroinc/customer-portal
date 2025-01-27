<?php

namespace Oro\Bundle\AddressValidationBundle\Test;

use Oro\Bundle\AddressValidationBundle\DependencyInjection\Configuration;
use Oro\Bundle\ConfigBundle\Tests\Functional\Traits\ConfigManagerAwareTestTrait;

/**
 * Trait used to enable and disable Address Validation Feature for Functional tests
 */
trait AddressValidationFeatureAwareTrait
{
    use ConfigManagerAwareTestTrait;

    protected function enableAddressValidationFeature(int $integrationId): void
    {
        $configManager = self::getConfigManager();
        $configManager->set(
            Configuration::getConfigKeyByName(Configuration::ADDRESS_VALIDATION_SERVICE),
            $integrationId
        );
        $configManager->flush();
    }

    protected function disableAddressValidationFeature(): void
    {
        $configManager = self::getConfigManager();
        $configManager->set(
            Configuration::getConfigKeyByName(Configuration::ADDRESS_VALIDATION_SERVICE),
            null
        );
        $configManager->flush();
    }
}
