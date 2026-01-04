<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\ApiBundle\Util\DependencyInjectionUtil;
use Oro\Bundle\FrontendBundle\DependencyInjection\OroFrontendExtension;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Component\DependencyInjection\ExtendedContainerBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class OroFrontendExtensionTest extends TestCase
{
    public function testLoad(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.environment', 'prod');
        DependencyInjectionUtil::setConfig($container, ['api_doc_views' => []]);

        $config = [
            'routes_to_expose' => ['expose_route1']
        ];

        $extension = new OroFrontendExtension();
        $extension->load([$config], $container);

        self::assertEquals(
            [
                [
                    'settings' => [
                        'resolved' => true,
                        'frontend_theme' => ['value' => '%oro_layout.default_active_theme%', 'scope' => 'app'],
                        'page_templates' => ['value' => [], 'scope' => 'app'],
                        'guest_access_enabled' => ['value' => true, 'scope' => 'app'],
                        'filter_value_selectors' => ['value' => 'dropdown', 'scope' => 'app'],
                        'web_api' => ['value' => false, 'scope' => 'app'],
                    ]
                ]
            ],
            $container->getExtensionConfig('oro_frontend')
        );
        self::assertEquals(
            $config['routes_to_expose'],
            $container->getDefinition('oro_frontend.extractor.frontend_exposed_routes_extractor')->getArgument(1)
        );

        $frontendHelperDef = $container->getDefinition('oro_frontend.request.frontend_helper');
        self::assertEquals(FrontendHelper::class, $frontendHelperDef->getClass());
        self::assertCount(3, $frontendHelperDef->getArguments());
    }

    public function testConfigurationForNotConfiguredFrontendSession(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.environment', 'prod');
        DependencyInjectionUtil::setConfig($container, ['api_doc_views' => []]);

        $extension = new OroFrontendExtension();
        $extension->load([], $container);

        self::assertSame([], $container->getParameter('oro_frontend.session.storage.options'));
    }

    public function testConfigurationForFrontendSession(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.environment', 'prod');
        DependencyInjectionUtil::setConfig($container, ['api_doc_views' => []]);

        $config = [
            'session' => [
                'name'            => 'TEST',
                'cookie_lifetime' => null,
                'cookie_path'     => '/test'
            ]
        ];

        $extension = new OroFrontendExtension();
        $extension->load([$config], $container);

        self::assertEquals(
            [
                'name'            => 'TEST',
                'cookie_path'     => '/test',
                'cookie_samesite' => 'lax',
            ],
            $container->getParameter('oro_frontend.session.storage.options')
        );
    }

    public function testConfigurationForFrontendSessionWithFalseValues(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.environment', 'prod');
        DependencyInjectionUtil::setConfig($container, ['api_doc_views' => []]);

        $config = [
            'session' => [
                'name'            => 'TEST',
                'cookie_httponly' => false
            ]
        ];

        $extension = new OroFrontendExtension();
        $extension->load([$config], $container);

        self::assertEquals(
            [
                'name'            => 'TEST',
                'cookie_httponly' => false,
                'cookie_samesite' => 'lax',
            ],
            $container->getParameter('oro_frontend.session.storage.options')
        );
    }

    public function testConfigurationForFrontendApiViews(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.environment', 'prod');

        $apiConfig = [
            'api_doc_views' => [
                'backend_view1' => [
                    'html_formatter' => 'oro_api.api_doc.formatter.html_formatter'
                ],
                'frontend_view1' => [
                    'html_formatter' => 'oro_api.api_doc.formatter.html_formatter'
                ]
            ]
        ];
        DependencyInjectionUtil::setConfig($container, $apiConfig);

        $config = [
            'frontend_api' => [
                'api_doc_views' => ['frontend_view1']
            ]
        ];

        $extension = new OroFrontendExtension();
        $extension->load([$config], $container);

        self::assertTrue($container->getParameter('oro_frontend.debug_routes'));
        self::assertEquals(
            $config['frontend_api']['api_doc_views'],
            $container->getParameter('oro_frontend.api_doc.views')
        );
        $updatedApiConfig = DependencyInjectionUtil::getConfig($container);
        self::assertEquals(
            $apiConfig['api_doc_views']['backend_view1']['html_formatter'],
            $updatedApiConfig['api_doc_views']['backend_view1']['html_formatter']
        );
        self::assertEquals(
            'oro_frontend.api_doc.formatter.html_formatter',
            $updatedApiConfig['api_doc_views']['frontend_view1']['html_formatter']
        );
    }

    public function testShouldThrowExceptionIfFrontendApiDocViewIsUnknown(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            'The view "frontend_view1" defined in oro_frontend.frontend_api.api_doc_views is unknown.'
            . ' Check that it is configured in oro_api.api_doc_views.'
        );

        $container = new ContainerBuilder();
        $container->setParameter('kernel.environment', 'prod');

        $apiConfig = [
            'api_doc_views' => [
                'backend_view1' => []
            ]
        ];
        DependencyInjectionUtil::setConfig($container, $apiConfig);

        $config = [
            'frontend_api' => [
                'api_doc_views' => ['frontend_view1']
            ]
        ];

        $extension = new OroFrontendExtension();
        $extension->load([$config], $container);
    }

    public function testConfigurationForFrontendApiEmptyCors(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.environment', 'prod');
        DependencyInjectionUtil::setConfig($container, ['api_doc_views' => []]);

        $extension = new OroFrontendExtension();
        $extension->load([], $container);

        $corsSettingsDef = $container->getDefinition('oro_frontend.api.rest.cors_settings');
        self::assertSame(600, $corsSettingsDef->getArgument(0));
        self::assertSame([], $corsSettingsDef->getArgument(1));
        self::assertFalse($corsSettingsDef->getArgument(2));
        self::assertSame([], $corsSettingsDef->getArgument(3));
        self::assertSame([], $corsSettingsDef->getArgument(4));
    }

    public function testConfigurationForFrontendApiCors(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.environment', 'prod');
        DependencyInjectionUtil::setConfig($container, ['api_doc_views' => []]);

        $config = [
            'frontend_api' => [
                'cors' => [
                    'preflight_max_age' => 123,
                    'allow_origins' => ['https://foo.com'],
                    'allow_headers' => ['AllowHeader1'],
                    'expose_headers' => ['ExposeHeader1'],
                    'allow_credentials' => true
                ]
            ]
        ];

        $extension = new OroFrontendExtension();
        $extension->load([$config], $container);

        $corsSettingsDef = $container->getDefinition('oro_frontend.api.rest.cors_settings');
        self::assertSame(
            $config['frontend_api']['cors']['preflight_max_age'],
            $corsSettingsDef->getArgument(0)
        );
        self::assertSame(
            $config['frontend_api']['cors']['allow_origins'],
            $corsSettingsDef->getArgument(1)
        );
        self::assertSame(
            $config['frontend_api']['cors']['allow_credentials'],
            $corsSettingsDef->getArgument(2)
        );
        self::assertSame(
            $config['frontend_api']['cors']['allow_headers'],
            $corsSettingsDef->getArgument(3)
        );
        self::assertSame(
            $config['frontend_api']['cors']['expose_headers'],
            $corsSettingsDef->getArgument(4)
        );
    }

    public function testPrependSecurity(): void
    {
        $configs = [
            [
            ],
            [
                'firewalls' => []
            ],
            [
                'firewalls' => [
                    'test1' => []
                ]
            ],
            [
                'firewalls' => [
                    'test2' => [
                        'pattern' => '%oro_api.rest.pattern%'
                    ],
                    'test3' => [
                        'pattern' => '%oro_api.rest.pattern%'
                    ],
                    'test4' => [
                        'pattern' => '/'
                    ],
                    'frontend_test3' => [
                        'pattern' => '%oro_api.rest.pattern%'
                    ]
                ]
            ]
        ];

        $expected = $configs;
        $expected[3]['firewalls']['test2']['pattern'] = '^/admin/api/(?!(rest|doc)($|/.*))';
        $expected[3]['firewalls']['test3']['pattern'] = '^/admin/api/(?!(rest|doc)($|/.*))';

        $container = new ExtendedContainerBuilder();
        $container->setParameter('kernel.environment', 'prod');
        $container->setParameter('web_backend_prefix', '/admin');
        $container->setParameter('oro_api.rest.prefix', '/api/');
        $container->setParameter('oro_api.rest.pattern', '^/api/(?!(rest|doc)($|/.*))');
        $container->setExtensionConfig('security', $configs);

        $extension = new OroFrontendExtension();
        $extension->prepend($container);

        self::assertEquals($expected, $container->getExtensionConfig('security'));
    }

    public function testPrependFosRest(): void
    {
        $configs = [
            [
                'view' => []
            ],
            [
                'view' => [],
                'format_listener' => []
            ],
            [
                'view' => [],
                'format_listener' => [
                    'rules' => []
                ]
            ],
            [
                'format_listener' => [
                    'rules' => [
                        ['path' => '%oro_api.rest.pattern%', 'prefer_extension' => false],
                        ['path' => '^/api/rest', 'prefer_extension' => false],
                        ['path' => '^/', 'stop' => true]
                    ]
                ]
            ]
        ];

        $expected = $configs;
        $rules = $expected[3]['format_listener']['rules'];
        array_unshift($expected[3]['format_listener']['rules'], $rules[0]);
        array_unshift($expected[3]['format_listener']['rules'], $rules[1]);
        $expected[3]['format_listener']['rules'][0]['path'] = '^/admin/api/(?!(rest|doc)($|/.*))';
        $expected[3]['format_listener']['rules'][1]['path'] = '^/admin/api/rest';

        $container = new ExtendedContainerBuilder();
        $container->setParameter('kernel.environment', 'prod');
        $container->setParameter('web_backend_prefix', '/admin');
        $container->setParameter('oro_api.rest.prefix', '/api/');
        $container->setParameter('oro_api.rest.pattern', '^/api/(?!(rest|doc)($|/.*))');
        $container->setExtensionConfig('fos_rest', $configs);

        $extension = new OroFrontendExtension();
        $extension->prepend($container);

        self::assertEquals($expected, $container->getExtensionConfig('fos_rest'));
    }

    public function testValidateBackendPrefixWithNullValue(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('The "web_backend_prefix" parameter value should not be empty.');

        $container = new ExtendedContainerBuilder();
        $container->setParameter('kernel.environment', 'prod');
        $container->setParameter('web_backend_prefix', null);

        $extension = new OroFrontendExtension();
        $extension->prepend($container);
    }

    public function testValidateBackendPrefixWithEmptyValue(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('The "web_backend_prefix" parameter value should not be empty.');

        $container = new ExtendedContainerBuilder();
        $container->setParameter('kernel.environment', 'prod');
        $container->setParameter('web_backend_prefix', '');

        $extension = new OroFrontendExtension();
        $extension->prepend($container);
    }

    public function testValidateBackendPrefixWhenItNotStartsWithSlash(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('The "web_backend_prefix" parameter should start with a "/" character.');

        $container = new ExtendedContainerBuilder();
        $container->setParameter('kernel.environment', 'prod');
        $container->setParameter('web_backend_prefix', 'admin');

        $extension = new OroFrontendExtension();
        $extension->prepend($container);
    }

    public function testValidateBackendPrefixWhenItEndsWithSlash(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('The "web_backend_prefix" parameter should not end with a "/" character.');

        $container = new ExtendedContainerBuilder();
        $container->setParameter('kernel.environment', 'prod');
        $container->setParameter('web_backend_prefix', '/admin/');

        $extension = new OroFrontendExtension();
        $extension->prepend($container);
    }

    public function testValidateBackendPrefixWithValidPrefixValue(): void
    {
        $container = new ExtendedContainerBuilder();
        $container->setParameter('kernel.environment', 'prod');
        $container->setParameter('web_backend_prefix', '/admin');

        $extension = new OroFrontendExtension();
        $extension->prepend($container);
    }

    public function testAddBackendPrefix(): void
    {
        $backendPrefix = '/admin';
        $originalConfig = [
            [
                'firewalls' => [
                    'main' => [
                        'oauth' => [
                            'resource_owners' => [
                                'test_resource_owner' => '/login/check-test-resource-owner'
                            ]
                        ]
                    ]
                ]
            ]
        ];
        $expectedConfig = [
            [
                'firewalls' => [
                    'main' => [
                        'oauth' => [
                            'resource_owners' => [
                                'test_resource_owner' => $backendPrefix . '/login/check-test-resource-owner'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $container = new ExtendedContainerBuilder();
        $container->setParameter('kernel.environment', 'prod');
        $container->setParameter('web_backend_prefix', $backendPrefix);
        $container->setExtensionConfig('security', $originalConfig);

        $extension = new OroFrontendExtension();
        $extension->prepend($container);

        $this->assertEquals($expectedConfig, $container->getExtensionConfig('security'));
    }

    public function testStorefrontEntityRoutesParameterIsSet(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.environment', 'prod');

        // Mock API bundle config to prevent "parameter not found" errors during extension loading
        DependencyInjectionUtil::setConfig($container, ['api_doc_views' => []]);

        $config = [
            'storefront_entity_routes' => [
                'App\Entity\Order' => [
                    'index' => 'oro_order_frontend_index',
                    'view' => 'oro_order_frontend_view',
                ],
                'App\Entity\Product' => [
                    'index' => 'oro_product_frontend_index',
                    'view' => 'oro_product_frontend_view',
                    'update' => 'oro_product_frontend_update',
                ]
            ]
        ];

        $extension = new OroFrontendExtension();
        $extension->load([$config], $container);

        self::assertTrue($container->hasParameter('oro_frontend.storefront_entity_routes'));
        self::assertEquals(
            $config['storefront_entity_routes'],
            $container->getParameter('oro_frontend.storefront_entity_routes')
        );
    }

    public function testStorefrontEntityRoutesParameterIsSetToEmptyArrayByDefault(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.environment', 'prod');

        // Mock API bundle config to prevent "parameter not found" errors during extension loading
        DependencyInjectionUtil::setConfig($container, ['api_doc_views' => []]);

        $extension = new OroFrontendExtension();
        $extension->load([], $container);

        self::assertTrue($container->hasParameter('oro_frontend.storefront_entity_routes'));
        self::assertEquals([], $container->getParameter('oro_frontend.storefront_entity_routes'));
    }
}
