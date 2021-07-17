<?php

namespace Oro\Bundle\CustomerBundle\Provider;

use Oro\Bundle\ConfigBundle\Config\Tree\GroupNodeDefinition;
use Oro\Bundle\ConfigBundle\Provider\AbstractProvider;

/**
 * Provides basic data for configuration form on the customer group level.
 */
class CustomerGroupConfigurationFormProvider extends AbstractProvider
{
    private const CUSTOMER_GROUP_CONFIGURATION_TREE_NAME = 'customer_group_configuration';

    private string $parentCheckboxLabel = 'oro.customer.customergroup.customer_group_configuration.use_default';

    /**
     * {@inheritdoc}
     */
    protected function getParentCheckboxLabel(): string
    {
        return $this->parentCheckboxLabel;
    }

    /**
     * {@inheritdoc}
     */
    public function getTree(): GroupNodeDefinition
    {
        return $this->getTreeData(self::CUSTOMER_GROUP_CONFIGURATION_TREE_NAME, self::CORRECT_FIELDS_NESTING_LEVEL);
    }

    /**
     * {@inheritdoc}
     */
    public function getJsTree(): array
    {
        return $this->getJsTreeData(self::CUSTOMER_GROUP_CONFIGURATION_TREE_NAME, self::CORRECT_MENU_NESTING_LEVEL);
    }

    public function setParentCheckboxLabel(string $label): void
    {
        $this->parentCheckboxLabel = $label;
    }
}
