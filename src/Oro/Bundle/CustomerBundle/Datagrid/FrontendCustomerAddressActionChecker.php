<?php

namespace Oro\Bundle\CustomerBundle\Datagrid;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;

/**
 * Checks and configures available actions for customer addresses in the frontend.
 *
 * This checker determines whether the map display action should be available for customer
 * addresses based on the system configuration for maps functionality.
 */
class FrontendCustomerAddressActionChecker
{
    /** @var ConfigManager */
    private $configManager;

    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    /**
     * @return array
     */
    public function checkActions()
    {
        return ($this->configManager->get('oro_customer.maps_enabled'))
            ? []
            : ['show_map' => false];
    }
}
