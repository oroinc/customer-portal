<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\FrontendBundle\DependencyInjection\OroFrontendExtension;
use Oro\Bundle\FrontendBundle\Tests\Unit\Fixtures\Bundle\TestBundle1\TestBundle1;
use Oro\Bundle\LocaleBundle\DependencyInjection\OroLocaleExtension;
use Oro\Component\Config\CumulativeResourceManager;
use Oro\Component\DependencyInjection\ExtendedContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class OroFrontendExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testLoad()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|ContainerBuilder $container */
        $container = $this->getMockBuilder(ContainerBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container->expects($this->once())
            ->method('prependExtensionConfig')
            ->with(OroFrontendExtension::ALIAS, $this->isType('array'));

        $container->expects($this->once())
            ->method('getParameter')
            ->with(OroLocaleExtension::PARAMETER_ADDRESS_FORMATS)
            ->willReturn([]);

        $routesExtractorDefinition = $this->createMock(Definition::class);
        $container->expects($this->once())
            ->method('getDefinition')
            ->with('oro_frontend.extractor.frontend_exposed_routes_extractor')
            ->willReturn($routesExtractorDefinition);

        $extension = new OroFrontendExtension();
        $extension->load([], $container);
    }

    public function testGetAlias()
    {
        $extension = new OroFrontendExtension();

        $this->assertEquals(OroFrontendExtension::ALIAS, $extension->getAlias());
    }

    public function testPrependFosRest()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|ExtendedContainerBuilder $container */
        $container = $this->getMockBuilder(ExtendedContainerBuilder::class)->disableOriginalConstructor()->getMock();
        $configs = [
            [
                'view' => [],
            ],
            [
                'view' => [],
                'format_listener' => [],
            ],
            [
                'view' => [],
                'format_listener' => [
                    'rules' => [],
                ],
            ],            [
                'format_listener' => [
                    'rules' => [
                        ['path' => '^/api/(?!(rest|doc)(/|$)+)'],
                        ['path' => '^/api/rest'],
                    ],
                ],
            ],
        ];
        $expected = $configs;
        $expected[3]['format_listener']['rules'][0]['path'] = '^/admin/api/(?!(rest|doc)(/|$)+)';

        $container->expects($this->once())->method('getExtensionConfig')->with('fos_rest')->willReturn($configs);
        $container->expects($this->once())->method('getParameter')->with('web_backend_prefix')->willReturn('/admin');
        $container->expects($this->once())->method('setExtensionConfig')->with('fos_rest', $expected);

        $extension = new OroFrontendExtension();
        $extension->prepend($container);
    }

    public function testPrependScreensConfigs()
    {
        CumulativeResourceManager::getInstance()
                                 ->clear()
                                 ->setBundles(['TestBundle1' => TestBundle1::class]);

        $container = new ContainerBuilder();
        $extension = new OroFrontendExtension();
        $extension->prepend($container);

        $expected = [[
            'themes' => [
                'sample_theme' => [
                    'config' => [
                        'screens' => [
                            'sample_screen' => [
                                'label' => 'Sample screen',
                                'hidingCssClass' => 'sample-css-class',
                            ],
                        ],
                    ],
                ],
            ],
        ]];
        $actual = $container->getExtensionConfig('oro_layout');

        $this->assertEquals($expected, $actual);
    }
}
