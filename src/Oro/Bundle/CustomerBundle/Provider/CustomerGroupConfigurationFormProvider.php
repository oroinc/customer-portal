<?php

namespace Oro\Bundle\CustomerBundle\Provider;

use Oro\Bundle\ConfigBundle\Provider\AbstractProvider;

/**
 * Provides configuration of a system configuration form on the customer group level.
 */
class CustomerGroupConfigurationFormProvider extends AbstractProvider
{
    private ?string $parentCheckboxLabel = null;

    public function setParentCheckboxLabel(string $label): void
    {
        $this->parentCheckboxLabel = $label;
    }

    /**
     * {@inheritdoc}
     */
    protected function getTreeName(): string
    {
        return 'customer_group_configuration';
    }

    /**
     * {@inheritdoc}
     */
    protected function getParentCheckboxLabel(): string
    {
        return $this->parentCheckboxLabel ?? 'oro.customer.customergroup.customer_group_configuration.use_default';
    }
}
