<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\FrontendBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testGetConfigTreeBuilder()
    {
        $configuration = new Configuration();

        $this->assertInstanceOf(
            'Symfony\Component\Config\Definition\Builder\TreeBuilder',
            $configuration->getConfigTreeBuilder()
        );
    }

    /**
     * @dataProvider processConfigurationDataProvider
     * @param array $configs
     * @param array $expected
     */
    public function testProcessConfiguration(array $configs, array $expected)
    {
        $configuration = new Configuration();
        $processor = new Processor();
        $this->assertEquals($expected, $processor->processConfiguration($configuration, $configs));
    }

    /**
     * @return array
     */
    public function processConfigurationDataProvider()
    {
        return [
            'empty' => [
                'configs'  => [[]],
                'expected' => [
                    'settings' => [
                        'resolved' => 1,
                        'frontend_theme' => [
                            'value' => '%oro_layout.default_active_theme%',
                            'scope' => 'app'
                        ],
                        'page_templates' => [
                            'value' => [],
                            'scope' => 'app'
                        ],
                        'guest_access_enabled' => [
                            'value' => true,
                            'scope' => 'app',
                        ],
                        'filter_value_selectors' => [
                            'value' => 'dropdown',
                            'scope' => 'app',
                        ],
                    ],
                    'routes_to_expose' => [],
                    'debug_routes' => true,
                    'frontend_api_doc_views' => []
                ]
            ]
        ];
    }
}
