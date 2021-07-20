<?php

namespace Oro\Bundle\CustomerBundle\Datagrid;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;

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
