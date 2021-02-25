<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\ApiBundle\Util\DependencyInjectionUtil;
use Oro\Bundle\FrontendBundle\DependencyInjection\OroFrontendExtension;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\FrontendBundle\Request\NotInstalledFrontendHelper;
use Oro\Component\DependencyInjection\ExtendedContainerBuilder;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class OroFrontendExtensionTest extends \PHPUnit\Framework\TestCase
{
    public function testLoad()
    {
        $container = $this->getContainerBuilder();
        $container->setParameter('installed', true);

        $config = [
            'routes_to_expose' => ['expose_route1']
        ];
        DependencyInjectionUtil::setConfig($container, ['api_doc_views' => []]);

        $extension = new OroFrontendExtension();
        $extension->load([$config], $container);

        $extensionConfig = $container->getExtensionConfig($extension->getAlias());
        self::assertCount(6, $extensionConfig[0]['settings']);
        self::assertEquals(
            $config['routes_to_expose'],
            $container->getDefinition('oro_frontend.extractor.frontend_exposed_routes_extractor')->getArgument(1)
        );

        $frontendHelperDef = $container->getDefinition('oro_frontend.request.frontend_helper');
        self::assertEquals(FrontendHelper::class, $frontendHelperDef->getClass());
        self::assertCount(2, $frontendHelperDef->getArguments());
    }

    public function testLoadForNotInstalled()
    {
        $container = $this->getContainerBuilder();
        DependencyInjectionUtil::setConfig($container, ['api_doc_views' => []]);

        $extension = new OroFrontendExtension();
        $extension->load([], $container);

        $frontendHelperDef = $container->getDefinition('oro_frontend.request.frontend_helper');
        self::assertEquals(NotInstalledFrontendHelper::class, $frontendHelperDef->getClass());
        self::assertCount(0, $frontendHelperDef->getArguments());
    }

    public function testConfigurationForNotConfiguredFrontendSession()
    {
        $container = $this->getContainerBuilder();

        $config = [];
        DependencyInjectionUtil::setConfig($container, ['api_doc_views' => []]);

        $extension = new OroFrontendExtension();
        $extension->load([$config], $container);

        self::assertSame([], $container->getParameter('oro_frontend.session.storage.options'));
    }

    public function testConfigurationForFrontendSession()
    {
        $container = $this->getContainerBuilder();

        $config = [
            'session' => [
                'name'            => 'TEST',
                'cookie_lifetime' => null,
                'cookie_path'     => '/test'
            ]
        ];
        DependencyInjectionUtil::setConfig($container, ['api_doc_views' => []]);

        $extension = new OroFrontendExtension();
        $extension->load([$config], $container);

        self::assertEquals(
            [
                'name'        => 'TEST',
                'cookie_path' => '/test'
            ],
            $container->getParameter('oro_frontend.session.storage.options')
        );
    }

    public function testConfigurationForFrontendApiViews()
    {
        $container = $this->getContainerBuilder();

        $config = [
            'frontend_api' => [
                'api_doc_views' => ['frontend_view1']
            ]
        ];
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

    public function testShouldThrowExceptionIfFrontendApiDocViewIsUnknown()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            'The view "frontend_view1" defined in oro_frontend.frontend_api.api_doc_views is unknown.'
            . ' Check that it is configured in oro_api.api_doc_views.'
        );

        $container = $this->getContainerBuilder();

        $config = [
            'frontend_api' => [
                'api_doc_views' => ['frontend_view1']
            ]
        ];
        $apiConfig = [
            'api_doc_views' => [
                'backend_view1' => []
            ]
        ];
        DependencyInjectionUtil::setConfig($container, $apiConfig);

        $extension = new OroFrontendExtension();
        $extension->load([$config], $container);
    }

    public function testConfigurationForFrontendApiEmptyCors()
    {
        $container = $this->getContainerBuilder();
        DependencyInjectionUtil::setConfig($container, ['api_doc_views' => []]);

        $config = [];

        $extension = new OroFrontendExtension();
        $extension->load([$config], $container);

        $corsSettingsDef = $container->getDefinition('oro_frontend.api.rest.cors_settings');
        self::assertSame(600, $corsSettingsDef->getArgument(0));
        self::assertSame([], $corsSettingsDef->getArgument(1));
        self::assertFalse($corsSettingsDef->getArgument(2));
        self::assertSame([], $corsSettingsDef->getArgument(3));
        self::assertSame([], $corsSettingsDef->getArgument(4));
    }

    public function testConfigurationForFrontendApiCors()
    {
        $container = $this->getContainerBuilder();
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

    public function testGetAlias()
    {
        $extension = new OroFrontendExtension();

        $this->assertEquals(OroFrontendExtension::ALIAS, $extension->getAlias());
    }

    public function testPrependSecurity()
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

    public function testPrependFosRest()
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
                        ['path' => '^/api/rest']
                    ]
                ]
            ]
        ];

        $expected = $configs;
        array_unshift(
            $expected[3]['format_listener']['rules'],
            $expected[3]['format_listener']['rules'][0]
        );
        $expected[3]['format_listener']['rules'][1]['path'] = '^/admin/api/(?!(rest|doc)($|/.*))';

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

    public function testValidateBackendPrefixWithNullValue()
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('The "web_backend_prefix" parameter value should not be null.');

        $container = new ExtendedContainerBuilder();
        $container->setParameter('kernel.environment', 'prod');
        $container->setParameter('web_backend_prefix', '');

        $extension = new OroFrontendExtension();
        $extension->prepend($container);
    }

    public function testValidateBackendPrefixWhenItNotStartsWithSlash()
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('The "web_backend_prefix" parameter should start with a "/" character.');

        $container = new ExtendedContainerBuilder();
        $container->setParameter('kernel.environment', 'prod');
        $container->setParameter('web_backend_prefix', 'admin');

        $extension = new OroFrontendExtension();
        $extension->prepend($container);
    }

    public function testValidateBackendPrefixWhenItEndsWithSlash()
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('The "web_backend_prefix" parameter should not end with a "/" character.');

        $container = new ExtendedContainerBuilder();
        $container->setParameter('kernel.environment', 'prod');
        $container->setParameter('web_backend_prefix', '/admin/');

        $extension = new OroFrontendExtension();
        $extension->prepend($container);
    }

    public function testValidateBackendPrefixWithValidPrefixValue()
    {
        $container = new ExtendedContainerBuilder();
        $container->setParameter('kernel.environment', 'prod');
        $container->setParameter('web_backend_prefix', '/admin');

        $extension = new OroFrontendExtension();
        $extension->prepend($container);
    }

    /**
     * @return ContainerBuilder
     */
    private function getContainerBuilder(): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.environment', 'prod');

        return $container;
    }
}
