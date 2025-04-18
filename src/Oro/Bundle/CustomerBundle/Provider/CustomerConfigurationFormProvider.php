<?php

namespace Oro\Bundle\CustomerBundle\Provider;

use Oro\Bundle\ConfigBundle\Provider\AbstractProvider;

/**
 * Provides configuration of a system configuration form on the customer level.
 */
class CustomerConfigurationFormProvider extends AbstractProvider
{
    #[\Override]
    protected function getTreeName(): string
    {
        return 'customer_configuration';
    }

    #[\Override]
    protected function getParentCheckboxLabel(): string
    {
        return 'oro.customer.customer_configuration.use_default';
    }
}
