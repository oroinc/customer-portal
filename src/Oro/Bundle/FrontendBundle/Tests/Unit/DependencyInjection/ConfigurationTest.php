<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\FrontendBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends TestCase
{
    private function processConfiguration(array $config): array
    {
        return (new Processor())->processConfiguration(new Configuration(), $config);
    }

    public function testProcessEmptyConfiguration(): void
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

    public function testProcessWithEmptyFrontendSessionName(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage(
            'The path "oro_frontend.session.name" cannot contain an empty value, but got "".'
        );

        $this->processConfiguration([['session' => ['name' => '']]]);
    }

    public function testProcessWithInvalidFrontendSessionName(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage(
            'Invalid configuration for path "oro_frontend.session.name":'
            . ' Session name "a+b" contains illegal character(s).'
        );

        $this->processConfiguration([['session' => ['name' => 'a+b']]]);
    }

    public function testProcessSessionConfiguration(): void
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

    /**
     * @dataProvider dataProvider
     */
    public function testGetConfigKeyByName(string $expected, string $key, string $separator): void
    {
        self::assertSame($expected, Configuration::getConfigKeyByName($key, $separator));
    }

    public function dataProvider(): \Generator
    {
        yield [
            'expected' => 'oro_frontend.key',
            'key' => 'key',
            'separator' => ConfigManager::SECTION_MODEL_SEPARATOR
        ];
        yield [
            'expected' => 'oro_frontend___key',
            'key' => 'key',
            'separator' => ConfigManager::SECTION_VIEW_SEPARATOR
        ];
        yield [
            'expected' => 'oro_frontend||key',
            'key' => 'key',
            'separator' => '||'
        ];
    }
}
