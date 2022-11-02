<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\FrontendBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends \PHPUnit\Framework\TestCase
{
    public function testGetConfigTreeBuilder()
    {
        $configuration = new Configuration();

        $this->assertInstanceOf(TreeBuilder::class, $configuration->getConfigTreeBuilder());
    }

    public function testProcessEmptyConfiguration()
    {
        $configs = [[]];
        $expected = [
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
                    'scope' => 'app'
                ],
                'filter_value_selectors' => [
                    'value' => 'dropdown',
                    'scope' => 'app'
                ],
                'web_api' => [
                    'value' => false,
                    'scope' => 'app'
                ]
            ],
            'routes_to_expose' => [],
            'debug_routes' => true,
            'frontend_api' => [
                'api_doc_views' => [],
                'cors' => [
                    'preflight_max_age' => 600,
                    'allow_origins' => [],
                    'allow_credentials' => false,
                    'allow_headers' => [],
                    'expose_headers' => []
                ]
            ]
        ];

        $configuration = new Configuration();
        $processor = new Processor();
        $this->assertEquals($expected, $processor->processConfiguration($configuration, $configs));
    }

    public function testProcessWithEmptyFrontendSessionName()
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage(
            'The path "oro_frontend.session.name" cannot contain an empty value, but got "".'
        );

        $configs = [['session' => ['name' => '']]];

        $configuration = new Configuration();
        $processor = new Processor();
        $processor->processConfiguration($configuration, $configs);
    }

    public function testProcessWithInvalidFrontendSessionName()
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage(
            'Invalid configuration for path "oro_frontend.session.name":'
            . ' Session name "a+b" contains illegal character(s).'
        );

        $configs = [['session' => ['name' => 'a+b']]];

        $configuration = new Configuration();
        $processor = new Processor();
        $processor->processConfiguration($configuration, $configs);
    }

    public function testProcessSessionConfiguration()
    {
        $configs = [
            [
                'session' => [
                    'name'            => 'TEST',
                    'cookie_lifetime' => 10,
                    'cookie_path'     => '/test',
                    'gc_maxlifetime'  => 20,
                    'gc_probability'  => 1,
                    'gc_divisor'      => 2,
                    'cookie_secure'   => 'auto',
                    'cookie_httponly' => true,
                    'cookie_samesite' => null
                ]
            ]
        ];
        $configuration = new Configuration();
        $processor = new Processor();
        $processedConfig = $processor->processConfiguration($configuration, $configs);
        $this->assertEquals($configs[0]['session'], $processedConfig['session']);
    }
}
