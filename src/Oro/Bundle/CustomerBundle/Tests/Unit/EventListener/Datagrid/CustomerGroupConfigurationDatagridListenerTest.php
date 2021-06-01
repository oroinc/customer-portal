<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\EventListener\Datagrid;

use Oro\Bundle\CustomerBundle\EventListener\Datagrid\CustomerGroupConfigurationDatagridListener;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use PHPUnit\Framework\TestCase;

class CustomerGroupConfigurationDatagridListenerTest extends TestCase
{
    public function testOnBuildBefore(): void
    {
        $gridConfig = DatagridConfiguration::create(
            [
                'properties' => [],
                'actions' => []
            ]
        );

        $dataGrid = $this->createMock(DatagridInterface::class);
        $event = new BuildBefore($dataGrid, $gridConfig);

        $listener = new CustomerGroupConfigurationDatagridListener();
        $listener->onBuildBefore($event);

        $this->assertEquals(
            [
                'type'   => 'url',
                'route'  => 'oro_customer_group_config',
                'params' => ['id']
            ],
            $gridConfig->offsetGetByPath('[properties][config_link]')
        );

        $this->assertEquals(
            [
                'type'         => 'navigate',
                'label'        => 'oro.customer.customergroup.grid.action.config',
                'link'         => 'config_link',
                'icon'         => 'cog',
                'acl_resource' => 'oro_customer_customer_group_update'
            ],
            $gridConfig->offsetGetByPath('[actions][config]')
        );
    }
}
