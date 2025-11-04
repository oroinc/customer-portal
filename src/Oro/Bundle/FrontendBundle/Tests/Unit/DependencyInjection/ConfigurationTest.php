<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\FrontendBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
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
            ],
            'storefront_entity_routes' => []
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

    /**
     * @dataProvider dataProvider
     */
    public function testGetConfigKeyByName(string $expected, string $key, string $separator)
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

    public function testProcessStorefrontEntityRoutesWithOnlyIndexRoute()
    {
        $configs = [
            [
                'storefront_entity_routes' => [
                    'App\Entity\Product' => [
                        'index' => 'oro_product_frontend_index',
                    ]
                ]
            ]
        ];

        $processedConfig = $this->processConfiguration($configs);

        $this->assertEquals(
            [
                'index' => 'oro_product_frontend_index',
            ],
            $processedConfig['storefront_entity_routes']['App\Entity\Product']
        );
        $this->assertArrayNotHasKey('view', $processedConfig['storefront_entity_routes']['App\Entity\Product']);
    }

    public function testProcessStorefrontEntityRoutesWithOnlyViewRoute()
    {
        $configs = [
            [
                'storefront_entity_routes' => [
                    'App\Entity\Product' => [
                        'view' => 'oro_product_frontend_view',
                    ]
                ]
            ]
        ];

        $processedConfig = $this->processConfiguration($configs);

        $this->assertEquals(
            [
                'view' => 'oro_product_frontend_view',
            ],
            $processedConfig['storefront_entity_routes']['App\Entity\Product']
        );
        $this->assertArrayNotHasKey('index', $processedConfig['storefront_entity_routes']['App\Entity\Product']);
    }

    public function testProcessStorefrontEntityRoutesWithBothIndexAndView()
    {
        $configs = [
            [
                'storefront_entity_routes' => [
                    'App\Entity\Order' => [
                        'index' => 'oro_order_frontend_index',
                        'view' => 'oro_order_frontend_view',
                    ]
                ]
            ]
        ];

        $processedConfig = $this->processConfiguration($configs);

        $this->assertEquals(
            [
                'index' => 'oro_order_frontend_index',
                'view' => 'oro_order_frontend_view',
            ],
            $processedConfig['storefront_entity_routes']['App\Entity\Order']
        );
    }

    public function testProcessStorefrontEntityRoutesWithAdditionalRoutes()
    {
        $configs = [
            [
                'storefront_entity_routes' => [
                    'App\Entity\Order' => [
                        'index' => 'oro_order_frontend_index',
                        'view' => 'oro_order_frontend_view',
                        'update' => 'oro_order_frontend_update',
                        'create' => 'oro_order_frontend_create',
                    ]
                ]
            ]
        ];

        $processedConfig = $this->processConfiguration($configs);

        $this->assertEquals(
            [
                'index' => 'oro_order_frontend_index',
                'view' => 'oro_order_frontend_view',
                'update' => 'oro_order_frontend_update',
                'create' => 'oro_order_frontend_create',
            ],
            $processedConfig['storefront_entity_routes']['App\Entity\Order']
        );
    }

    public function testProcessStorefrontEntityRoutesWithMultipleEntities()
    {
        $configs = [
            [
                'storefront_entity_routes' => [
                    'App\Entity\Order' => [
                        'index' => 'oro_order_frontend_index',
                        'view' => 'oro_order_frontend_view',
                    ],
                    'App\Entity\Product' => [
                        'index' => 'oro_product_frontend_index',
                    ]
                ]
            ]
        ];

        $processedConfig = $this->processConfiguration($configs);

        $this->assertCount(2, $processedConfig['storefront_entity_routes']);
        $this->assertArrayHasKey('App\Entity\Order', $processedConfig['storefront_entity_routes']);
        $this->assertArrayHasKey('App\Entity\Product', $processedConfig['storefront_entity_routes']);
    }

    public function testProcessStorefrontEntityRoutesFailsWithNeitherIndexNorView()
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage(
            "Either 'index' or 'view' route, or both, must be specified."
        );

        $this->processConfiguration([
            [
                'storefront_entity_routes' => [
                    'App\Entity\Product' => [
                        'update' => 'oro_product_frontend_update',
                    ]
                ]
            ]
        ]);
    }

    public function testProcessStorefrontEntityRoutesFailsWithEmptyArray()
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage(
            "Either 'index' or 'view' route, or both, must be specified."
        );

        $this->processConfiguration([
            [
                'storefront_entity_routes' => [
                    'App\Entity\Product' => []
                ]
            ]
        ]);
    }

    public function testProcessStorefrontEntityRoutesFailsWithBothRoutesNull()
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage(
            "Either 'index' or 'view' route, or both, must be specified."
        );

        $this->processConfiguration([
            [
                'storefront_entity_routes' => [
                    'App\Entity\Product' => [
                        'index' => null,
                        'view' => null,
                    ]
                ]
            ]
        ]);
    }
}
