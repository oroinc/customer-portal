<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\FrontendBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends \PHPUnit\Framework\TestCase
{
    private function processConfiguration(array $config): array
    {
        return (new Processor())->processConfiguration(new Configuration(), $config);
    }

    public function testProcessEmptyConfiguration()
    {
        $expected = [
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

        $processedConfig = $this->processConfiguration([]);
        unset($processedConfig['settings']);
        $this->assertEquals($expected, $processedConfig);
    }

    public function testProcessWithEmptyFrontendSessionName()
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage(
            'The path "oro_frontend.session.name" cannot contain an empty value, but got "".'
        );

        $this->processConfiguration([['session' => ['name' => '']]]);
    }

    public function testProcessWithInvalidFrontendSessionName()
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage(
            'Invalid configuration for path "oro_frontend.session.name":'
            . ' Session name "a+b" contains illegal character(s).'
        );

        $this->processConfiguration([['session' => ['name' => 'a+b']]]);
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
        $processedConfig = $this->processConfiguration($configs);
        $this->assertEquals($configs[0]['session'], $processedConfig['session']);
    }
}
