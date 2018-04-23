<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Oro\Bundle\CustomerBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test Configuration
     */
    public function testGetConfigTreeBuilder()
    {
        $configuration = new Configuration();

        $this->assertInstanceOf(TreeBuilder::class, $configuration->getConfigTreeBuilder());
    }

    /**
     * @dataProvider processConfigurationDataProvider
     * @param array $configs
     * @param array $expected
     */
    public function testProcessConfiguration(array $configs, array $expected)
    {
        $configuration = new Configuration();
        $processor     = new Processor();
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
                        'default_customer_owner' => [
                            'value' => 1,
                            'scope' => 'app'
                        ],
                        'anonymous_customer_group' => [
                            'value' => null,
                            'scope' => 'app'
                        ],
                        'registration_allowed' => [
                            'value' => true,
                            'scope' => 'app'
                        ],
                        'registration_link_enabled' => [
                            'value' => true,
                            'scope' => 'app'
                        ],
                        'confirmation_required' => [
                            'value' => true,
                            'scope' => 'app'
                        ],
                        'send_password_in_welcome_email' => [
                            'value' => false,
                            'scope' => 'app'
                        ],
                        'registration_instructions_enabled' => [
                            'value' => false,
                            'scope' => 'app',
                        ],
                        'registration_instructions_text' => [
                            'value' =>
                                'To register for a new account, contact a sales representative at 1 (800) 555-0123',
                            'scope' => 'app',
                        ],
                        'company_name_field_enabled' => [
                            'value' => true,
                            'scope' => 'app'
                        ],
                        'user_menu_show_items' => [
                            'value' => 'all_at_once',
                            'scope' => 'app',
                        ],
                        'customer_visitor_cookie_lifetime_days' => [
                            'value' => 30,
                            'scope' => 'app',
                        ],
                        'maps_enabled' => [
                            'value' => true,
                            'scope' => 'app',
                        ],
                        'api_key_generation_enabled' => [
                            'value' => true,
                            'scope' => 'app',
                        ]
                    ]
                ]
            ]
        ];
    }
}
