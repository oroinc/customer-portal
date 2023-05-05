<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\EventListener\Datagrid;

use Oro\Bundle\CustomerBundle\EventListener\Datagrid\CustomerUserDatagridListener;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\ParameterBag;
use Oro\Bundle\DataGridBundle\Event\PreBuild;

class CustomerUserDatagridListenerTest extends \PHPUnit\Framework\TestCase
{
    /** @var CustomerUserDatagridListener */
    private $listener;

    protected function setUp(): void
    {
        $this->listener = new CustomerUserDatagridListener();
    }

    /**
     * @dataProvider dataProvider
     */
    public function testCustomerLimitations(ParameterBag $parameters, DatagridConfiguration $expectedConfig)
    {
        $event = new PreBuild(DatagridConfiguration::create([]), $parameters);

        $this->listener->onBuildBefore($event);

        $this->assertEquals($expectedConfig->toArray(), $event->getConfig()->toArray());
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function dataProvider(): array
    {
        return [
            'hasRole user condition only' => [
                new ParameterBag([]),
                DatagridConfiguration::create([
                    'source' => [
                        'query' => [
                            'select' => [
                                '(CASE WHEN user.id IN (:data_in) AND user.id NOT IN (:data_not_in) ' .
                                'THEN true ELSE false END) as hasRole',
                            ],
                        ],
                    ],
                ])
            ],
            'hasRole role condition' => [
                new ParameterBag(['role' => 1]),
                DatagridConfiguration::create([
                    'source' => [
                        'query' => [
                            'select' => [
                                '(CASE WHEN (:role MEMBER OF user.userRoles OR user.id IN (:data_in)) ' .
                                'AND user.id NOT IN (:data_not_in) THEN true ELSE false END) as hasRole',
                            ],
                        ],
                        'bind_parameters' => ['role'],
                    ],
                ])
            ],
            'invalid additional parameters' => [
                new ParameterBag([ParameterBag::ADDITIONAL_PARAMETERS => true]),
                DatagridConfiguration::create([
                    'source' => [
                        'query' => [
                            'select' => [
                                '(CASE WHEN user.id IN (:data_in) AND user.id NOT IN (:data_not_in) ' .
                                'THEN true ELSE false END) as hasRole',
                            ],
                        ],
                    ],
                ])
            ],
            'dont limit customer without customer id' => [
                new ParameterBag([ParameterBag::ADDITIONAL_PARAMETERS => []]),
                DatagridConfiguration::create([
                    'source' => [
                        'query' => [
                            'select' => [
                                '(CASE WHEN user.id IN (:data_in) AND user.id NOT IN (:data_not_in) ' .
                                'THEN true ELSE false END) as hasRole',
                            ],
                        ],
                    ],
                ])
            ],
            'limit customer with customer id' => [
                new ParameterBag([ParameterBag::ADDITIONAL_PARAMETERS => [], 'customer' => 1]),
                DatagridConfiguration::create([
                    'source' => [
                        'query' => [
                            'select' => [
                                '(CASE WHEN user.id IN (:data_in) AND user.id NOT IN (:data_not_in) ' .
                                'THEN true ELSE false END) as hasRole',
                            ],
                            'where' => ['or' => ['user.customer = :customer']],
                        ],
                        'bind_parameters' => ['customer'],
                    ],
                ])
            ],
            'dont limit customer if change triggered' => [
                new ParameterBag([
                    ParameterBag::ADDITIONAL_PARAMETERS => ['changeCustomerAction' => true,],
                    'customer' => 1,
                ]),
                DatagridConfiguration::create([
                    'source' => [
                        'query' => [
                            'select' => [
                                '(CASE WHEN user.id IN (:data_in) AND user.id NOT IN (:data_not_in) ' .
                                'THEN true ELSE false END) as hasRole',
                            ],
                        ],
                    ],
                ])
            ],
            'limit new customer if change triggered' => [
                new ParameterBag([
                    ParameterBag::ADDITIONAL_PARAMETERS => ['changeCustomerAction' => true, 'newCustomer' => 1],
                ]),
                DatagridConfiguration::create([
                    'source' => [
                        'query' => [
                            'select' => [
                                '(CASE WHEN user.id IN (:data_in) AND user.id NOT IN (:data_not_in) ' .
                                'THEN true ELSE false END) as hasRole',
                            ],
                            'where' => ['or' => ['user.customer = :newCustomer']],
                        ],
                        'bind_parameters' => [['name' => 'newCustomer', 'path' => '_parameters.newCustomer']],
                    ],
                ])
            ],
        ];
    }
}
