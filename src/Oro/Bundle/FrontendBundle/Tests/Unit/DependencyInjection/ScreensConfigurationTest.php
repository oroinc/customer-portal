<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\FrontendBundle\DependencyInjection\ScreensConfiguration;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

class ScreensConfigurationTest extends \PHPUnit\Framework\TestCase
{
    public function testGetConfigTreeBuilder()
    {
        $configuration = new ScreensConfiguration();

        static::assertInstanceOf(TreeBuilder::class, $configuration->getConfigTreeBuilder());
    }

    /**
     * @dataProvider processConfigurationDataProvider
     *
     * @param array $configs
     * @param array $expected
     */
    public function testProcessConfiguration(array $configs, array $expected)
    {
        $configuration = new ScreensConfiguration();
        $processor = new Processor();

        static::assertEquals($expected, $processor->processConfiguration($configuration, $configs));
    }

    /**
     * @return array
     */
    public function processConfigurationDataProvider()
    {
        $emptyConfig = [];
        $emptyScreensNode = [
            'sample_theme' => [
                'config' => [
                    'screens' => [],
                ],
            ],
        ];
        $normalConfig = [
            'sample_theme' => [
                'config' => [
                    'screens' => [
                        'sample_screen' => [
                            'label' => 'Label for sample screen',
                            'hidingCssClass' => 'sample-hiding-css-class',
                        ],
                    ],
                ],
            ],
        ];

        return [
            'whole empty config' => [
                'configs' => [$emptyConfig],
                'expected' => [
                    'themes' => $emptyConfig,
                ],
            ],
            'empty screens node' => [
                'configs' => [['themes' => $emptyScreensNode]],
                'expected' => [
                    'themes' => $emptyScreensNode,
                ],
            ],
            'normal config' => [
                'configs' => [['themes' => $normalConfig]],
                'expected' => [
                    'themes' => $normalConfig,
                ],
            ],
        ];
    }

    /**
     * @dataProvider processInvalidConfigurationDataProvider
     *
     * @param array $configs
     */
    public function testProcessInvalidConfiguration(array $configs)
    {
        $configuration = new ScreensConfiguration();
        $processor = new Processor();

        static::expectException(InvalidConfigurationException::class);
        $processor->processConfiguration($configuration, $configs);
    }

    /**
     * @return array
     */
    public function processInvalidConfigurationDataProvider()
    {
        return [
            'missing label' => [
                'configs' => [
                    [
                        'themes' => [
                            'sample_theme' => [
                                'screens' => [
                                    'sample_screen' => [
                                        'hidingCssClass' => 'sample-hiding-css-class',
                                    ],
                                ],
                            ],
                        ],
                    ]
                ]
            ],
            'missing hidingCssClass' => [
                'configs' => [
                    [
                        'themes' => [
                            'sample_theme' => [
                                'screens' => [
                                    'sample_screen' => [
                                        'label' => 'Sample screen label',
                                    ],
                                ],
                            ],
                        ],
                    ]
                ]
            ],
            'missing label and hidingCssClass' => [
                'configs' => [
                    [
                        'themes' => [
                            'sample_theme' => [
                                'screens' => [
                                    'sample_screen' => [],
                                ],
                            ],
                        ],
                    ]
                ]
            ],
        ];
    }
}
