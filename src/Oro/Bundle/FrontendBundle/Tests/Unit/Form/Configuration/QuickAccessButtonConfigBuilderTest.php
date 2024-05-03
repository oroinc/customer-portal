<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Form\Configuration;

use Oro\Bundle\FrontendBundle\Form\Configuration\QuickAccessButtonConfigBuilder;
use Oro\Bundle\FrontendBundle\Form\Type\QuickAccessButtonConfigType;
use Oro\Bundle\FrontendBundle\Model\QuickAccessButtonConfig;
use Oro\Bundle\ThemeBundle\Form\Configuration\ConfigurationChildBuilderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

final class QuickAccessButtonConfigBuilderTest extends TestCase
{
    private FormBuilderInterface $formBuilder;
    private Packages|MockObject $packages;
    private QuickAccessButtonConfigBuilder $quickAccessButtonConfigBuilder;

    protected function setUp(): void
    {
        $this->formBuilder = $this->createMock(FormBuilderInterface::class);
        $this->packages = $this->createMock(Packages::class);
        $this->quickAccessButtonConfigBuilder = new QuickAccessButtonConfigBuilder($this->packages);
    }

    /**
     * @dataProvider getSupportsDataProvider
     */
    public function testSupports(string $type, bool $expectedResult): void
    {
        self::assertEquals(
            $expectedResult,
            $this->quickAccessButtonConfigBuilder->supports(['type' => $type])
        );
    }

    public function getSupportsDataProvider(): array
    {
        return [
            ['unknown_type', false],
            [QuickAccessButtonConfigBuilder::getType(), true],
        ];
    }

    /**
     * @dataProvider optionDataProvider
     */
    public function testThatOptionBuiltCorrectly(array $option, array $expected): void
    {
        $this->formBuilder
            ->expects(self::once())
            ->method('add')
            ->with(
                $expected['name'],
                $expected['form_type'],
                $expected['options']
            );

        $this->quickAccessButtonConfigBuilder->buildOption($this->formBuilder, $option);
    }

    /**
     * @dataProvider finishViewDataProvider
     */
    public function testThatFinishViewCorrectly(
        array $themeOption,
        mixed $data,
        array $assets,
        array $expectedAttr,
        array $expectedGroupAttr
    ): void {
        $formView = new FormView();
        $form = $this->createMock(FormInterface::class);
        $form->expects(self::any())
            ->method('getData')
            ->willReturn($data);

        if ($assets['count'] > 0) {
            $this->packages
                ->expects(self::exactly($assets['count']))
                ->method('getUrl')
                ->withConsecutive(...$assets['url'])
                ->willReturnOnConsecutiveCalls(...$assets['fullUrl']);
        } else {
            $this->packages
                ->expects(self::never())
                ->method('getUrl');
        }

        $this->quickAccessButtonConfigBuilder->finishView(
            $formView,
            $form,
            [],
            $themeOption
        );

        self::assertEquals($expectedAttr, $formView->vars['attr']);
        self::assertEquals($expectedGroupAttr, $formView->vars['group_attr'] ?? []);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function finishViewDataProvider(): array
    {
        return [
            'without previews key' => [
                'themeOption' => [],
                'data' => null,
                'assets' => [
                    'count' => 0,
                    'url' => [],
                    'fullUrl' => []
                ],
                'expectedAttr' => [],
                'expectedGroupAttr' => [],
            ],
            'with empty previews key' => [
                'themeOption' => ['previews' => []],
                'data' => null,
                'assets' => [
                    'count' => 0,
                    'url' => [],
                    'fullUrl' => []
                ],
                'expectedAttr' => [],
                'expectedGroupAttr' => [],
            ],
            'with default previews key' => [
                'themeOption' => [
                    'previews' => [
                        ConfigurationChildBuilderInterface::DEFAULT_PREVIEW_KEY => 'default.png'
                    ]
                ],
                'data' => null,
                'assets' => [
                    'count' => 2,
                    'url' => [['default.png'], ['default.png']],
                    'fullUrl' => ['/default.png', '/default.png']
                ],
                'expectedAttr' => [
                    'data-default-preview' => '/default.png',
                    'data-preview' => '/default.png'
                ],
                'expectedGroupAttr' => [
                    'data-page-component-view' => QuickAccessButtonConfigBuilder::VIEW_MODULE_NAME,
                    'data-page-component-options' => [
                        'autoRender' => true,
                        'previewSource' => '/default.png',
                        'defaultPreview' => '/default.png'
                    ]
                ]
            ],
            'with previews keys' => [
                'themeOption' => [
                    'previews' => [
                        'menu' => 'menu.png',
                        'web_catalog_node' => 'web_catalog_node.png',
                    ]
                ],
                'data' => null,
                'assets' => [
                    'count' => 2,
                    'url' => [['menu.png'], ['web_catalog_node.png']],
                    'fullUrl' => ['/menu.png', '/web_catalog_node.png']
                ],
                'expectedAttr' => [
                    'data-preview-menu' => '/menu.png',
                    'data-preview-web_catalog_node' => '/web_catalog_node.png'
                ],
                'expectedGroupAttr' => [
                    'data-page-component-view' => QuickAccessButtonConfigBuilder::VIEW_MODULE_NAME,
                    'data-page-component-options' => [
                        'autoRender' => true,
                        'previewSource' => '',
                        'defaultPreview' => ''
                    ]
                ],
            ],
            'with form data' => [
                'themeOption' => [
                    'previews' => [
                        'menu' => 'menu.png',
                        'web_catalog_node' => 'web_catalog_node.png',
                    ]
                ],
                'data' => 'web_catalog_node',
                'assets' => [
                    'count' => 3,
                    'url' => [['web_catalog_node.png'], ['menu.png'], ['web_catalog_node.png']],
                    'fullUrl' => ['/web_catalog_node.png', '/menu.png', '/web_catalog_node.png']
                ],
                'expectedAttr' => [
                    'data-preview' => '/web_catalog_node.png',
                    'data-preview-menu' => '/menu.png',
                    'data-preview-web_catalog_node' => '/web_catalog_node.png'
                ],
                'expectedGroupAttr' => [
                    'data-page-component-view' => QuickAccessButtonConfigBuilder::VIEW_MODULE_NAME,
                    'data-page-component-options' => [
                        'autoRender' => true,
                        'previewSource' => '/web_catalog_node.png',
                        'defaultPreview' => ''
                    ]
                ],
            ],
            'with none QuickAccessButtonConfig form data' => [
                'themeOption' => [
                    'previews' => [
                        'menu' => 'menu.png',
                        'web_catalog_node' => 'web_catalog_node.png',
                    ]
                ],
                'data' => new QuickAccessButtonConfig(),
                'assets' => [
                    'count' => 2,
                    'url' => [['menu.png'], ['web_catalog_node.png']],
                    'fullUrl' => ['/menu.png', '/web_catalog_node.png']
                ],
                'expectedAttr' => [
                    'data-preview-menu' => '/menu.png',
                    'data-preview-web_catalog_node' => '/web_catalog_node.png'
                ],
                'expectedGroupAttr' => [
                    'data-page-component-view' => QuickAccessButtonConfigBuilder::VIEW_MODULE_NAME,
                    'data-page-component-options' => [
                        'autoRender' => true,
                        'previewSource' => '',
                        'defaultPreview' => ''
                    ]
                ],
            ],
            'with menu QuickAccessButtonConfig form data' => [
                'themeOption' => [
                    'previews' => [
                        'menu' => 'menu.png',
                        'web_catalog_node' => 'web_catalog_node.png',
                    ]
                ],
                'data' => (new QuickAccessButtonConfig())->setType(QuickAccessButtonConfig::TYPE_MENU),
                'assets' => [
                    'count' => 3,
                    'url' => [['menu.png'], ['menu.png'], ['web_catalog_node.png']],
                    'fullUrl' => ['/menu.png', '/menu.png', '/web_catalog_node.png']
                ],
                'expectedAttr' => [
                    'data-preview' => '/menu.png',
                    'data-preview-menu' => '/menu.png',
                    'data-preview-web_catalog_node' => '/web_catalog_node.png'
                ],
                'expectedGroupAttr' => [
                    'data-page-component-view' => QuickAccessButtonConfigBuilder::VIEW_MODULE_NAME,
                    'data-page-component-options' => [
                        'autoRender' => true,
                        'previewSource' => '/menu.png',
                        'defaultPreview' => ''
                    ]
                ],
            ],
            'with web_catalog_node QuickAccessButtonConfig form data' => [
                'themeOption' => [
                    'previews' => [
                        'menu' => 'menu.png',
                        'web_catalog_node' => 'web_catalog_node.png',
                    ]
                ],
                'data' => (new QuickAccessButtonConfig())->setType(QuickAccessButtonConfig::TYPE_WEB_CATALOG_NODE),
                'assets' => [
                    'count' => 3,
                    'url' => [['web_catalog_node.png'], ['menu.png'], ['web_catalog_node.png']],
                    'fullUrl' => ['/web_catalog_node.png', '/menu.png', '/web_catalog_node.png']
                ],
                'expectedAttr' => [
                    'data-preview' => '/web_catalog_node.png',
                    'data-preview-menu' => '/menu.png',
                    'data-preview-web_catalog_node' => '/web_catalog_node.png'
                ],
                'expectedGroupAttr' => [
                    'data-page-component-view' => QuickAccessButtonConfigBuilder::VIEW_MODULE_NAME,
                    'data-page-component-options' => [
                        'autoRender' => true,
                        'previewSource' => '/web_catalog_node.png',
                        'defaultPreview' => ''
                    ]
                ],
            ],
            'with option default data' => [
                'themeOption' => [
                    'default' => 'menu',
                    'previews' => [
                        'menu' => 'menu.png',
                        'web_catalog_node' => 'web_catalog_node.png',
                    ]
                ],
                'data' => null,
                'assets' => [
                    'count' => 3,
                    'url' => [['menu.png'], ['menu.png'], ['web_catalog_node.png']],
                    'fullUrl' => ['/menu.png', '/menu.png', '/web_catalog_node.png']
                ],
                'expectedAttr' => [
                    'data-preview' => '/menu.png',
                    'data-preview-menu' => '/menu.png',
                    'data-preview-web_catalog_node' => '/web_catalog_node.png'
                ],
                'expectedGroupAttr' => [
                    'data-page-component-view' => QuickAccessButtonConfigBuilder::VIEW_MODULE_NAME,
                    'data-page-component-options' => [
                        'autoRender' => true,
                        'previewSource' => '/menu.png',
                        'defaultPreview' => ''
                    ]
                ]
            ],
            'with option default data and key when preview is missed' => [
                'themeOption' => [
                    'default' => 'menu',
                    'previews' => [
                        ConfigurationChildBuilderInterface::DEFAULT_PREVIEW_KEY => 'default.png',
                        'web_catalog_node' => 'web_catalog_node.png',
                    ]
                ],
                'data' => null,
                'assets' => [
                    'count' => 3,
                    'url' => [['default.png'], ['default.png'], ['web_catalog_node.png']],
                    'fullUrl' => ['/default.png', '/default.png', '/web_catalog_node.png']
                ],
                'expectedAttr' => [
                    'data-default-preview' => '/default.png',
                    'data-preview' => '/default.png',
                    'data-preview-web_catalog_node' => '/web_catalog_node.png'
                ],
                'expectedGroupAttr' => [
                    'data-page-component-view' => QuickAccessButtonConfigBuilder::VIEW_MODULE_NAME,
                    'data-page-component-options' => [
                        'autoRender' => true,
                        'previewSource' => '/default.png',
                        'defaultPreview' => '/default.png'
                    ]
                ]
            ],
            'with form data, option default data and key' => [
                'themeOption' => [
                    'default' => 'menu',
                    'previews' => [
                        ConfigurationChildBuilderInterface::DEFAULT_PREVIEW_KEY => 'default.png',
                        'menu' => 'menu.png',
                        'web_catalog_node' => 'web_catalog_node.png',
                    ]
                ],
                'data' => 'menu',
                'assets' => [
                    'count' => 4,
                    'url' => [['default.png'], ['menu.png'], ['menu.png'], ['web_catalog_node.png']],
                    'fullUrl' => ['/default.png', '/menu.png', '/menu.png', '/web_catalog_node.png']
                ],
                'expectedAttr' => [
                    'data-default-preview' => '/default.png',
                    'data-preview' => '/menu.png',
                    'data-preview-menu' => '/menu.png',
                    'data-preview-web_catalog_node' => '/web_catalog_node.png'
                ],
                'expectedGroupAttr' => [
                    'data-page-component-view' => QuickAccessButtonConfigBuilder::VIEW_MODULE_NAME,
                    'data-page-component-options' => [
                        'autoRender' => true,
                        'previewSource' => '/menu.png',
                        'defaultPreview' => '/default.png'
                    ]
                ]
            ],
        ];
    }

    private function optionDataProvider(): array
    {
        return [
            'no previews' => [
                [
                    'name' => 'general-quick-access-button',
                    'label' => 'Quick Access Button',
                    'type' => QuickAccessButtonConfigBuilder::getType(),
                    'default' => null
                ],
                [
                    'name' => 'general-quick-access-button',
                    'form_type' => QuickAccessButtonConfigType::class,
                    'options' => [
                        'label' => 'Quick Access Button',
                        'attr' => [],
                        'by_reference' => false,
                        'empty_data' => new QuickAccessButtonConfig()
                    ]
                ]
            ],
        ];
    }
}
