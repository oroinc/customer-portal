<?php

namespace Oro\Bundle\FrontendBundle\Datagrid\Extension;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\Common\MetadataObject;
use Oro\Bundle\DataGridBundle\Extension\AbstractExtension;

/**
 * Sets "frontend" flag for the mass actions if the datagrid is also marked with "frontend" flag.
 */
class FrontendMassActionDatagridExtension extends AbstractExtension
{
    private const ACTION_KEY = 'mass_actions';

    /**
     * {@inheritdoc}
     */
    public function isApplicable(DatagridConfiguration $config): bool
    {
        return parent::isApplicable($config) && $this->isFrontendGrid($config);
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority(): int
    {
        //  Must be called before Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionExtension.
        return 210;
    }

    public function visitMetadata(DatagridConfiguration $config, MetadataObject $data): void
    {
        $massActionsConfig = $config->offsetGetOr(self::ACTION_KEY, []);
        foreach ($massActionsConfig as $actionName => $actionConfig) {
            $actionConfig['frontend'] = true;
            $massActionsConfig[$actionName] = $actionConfig;
        }

        $config->offsetSet(self::ACTION_KEY, $massActionsConfig);

        parent::visitMetadata($config, $data);
    }

    private function isFrontendGrid(DatagridConfiguration $config): bool
    {
        return (bool)$config->offsetGetByPath(FrontendDatagridExtension::FRONTEND_OPTION_PATH, false);
    }
}
