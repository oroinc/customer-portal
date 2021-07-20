<?php

namespace Oro\Bundle\CustomerBundle\EventListener\Datagrid;

use Oro\Bundle\DataGridBundle\Event\BuildBefore;

/**
 * Add configuration action on customer grid.
 */
class CustomerGroupConfigurationDatagridListener
{
    public function onBuildBefore(BuildBefore $event): void
    {
        $config = $event->getConfig();
        $config->offsetSetByPath(
            '[properties][config_link]',
            [
                'type'   => 'url',
                'route'  => 'oro_customer_group_config',
                'params' => ['id']
            ]
        );
        $config->offsetSetByPath(
            '[actions][config]',
            [
                'type'         => 'navigate',
                'label'        => 'oro.customer.customergroup.grid.action.config',
                'link'         => 'config_link',
                'icon'         => 'cog',
                'acl_resource' => 'oro_customer_customer_group_update'
            ]
        );
    }
}
