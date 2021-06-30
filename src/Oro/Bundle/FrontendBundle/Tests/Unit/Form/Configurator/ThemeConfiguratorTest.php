<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Form\Configurator;

use Oro\Bundle\ConfigBundle\Config\ConfigBag;
use Oro\Bundle\FrontendBundle\Form\Configurator\ThemeConfigurator;
use Oro\Bundle\FrontendBundle\Form\Type\PageTemplateFormFieldType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ThemeConfiguratorTest extends \PHPUnit\Framework\TestCase
{
    /** @var ThemeConfigurator */
    private $themeConfigurator;

    /** @var ConfigBag|\PHPUnit\Framework\MockObject\MockObject */
    private $configBag;

    /** @var AuthorizationCheckerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $authorizationChecker;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->configBag = $this->createMock(ConfigBag::class);
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $this->themeConfigurator = new ThemeConfigurator($this->configBag, $this->authorizationChecker);
    }

    /**
     * @dataProvider fieldsRootDataProvider
     *
     * @param array $fieldsRoot
     * @param array $expectedOptions
     */
    public function testConfigure(array $fieldsRoot, array $expectedOptions)
    {
        $this->configBag
            ->expects($this->once())
            ->method('getFieldsRoot')
            ->with('oro_frontend.page_templates')
            ->willReturn($fieldsRoot);

        $builder = $this->createMock(FormBuilderInterface::class);

        $expectedName = 'oro_frontend___page_templates';
        $builder->expects($this->once())
            ->method('add')
            ->with($expectedName, PageTemplateFormFieldType::class, $expectedOptions);

        $this->themeConfigurator->configure($builder, []);
    }

    /**
     * @return array
     */
    public function fieldsRootDataProvider()
    {
        return [
            'default' => [
                'fieldsRoot' => [
                    'type' => 'target_field_type',
                    'page_reload' => false,
                ],
                'expectedOptions' => [
                    'target_field_type' => 'target_field_type',
                    'target_field_options' => [],
                ]
            ],
            'with options' => [
                'fieldsRoot' => [
                    'type' => 'target_field_type',
                    'options' => [
                        'test_1' => 'Test 1',
                        'test_2' => 'Test 2',
                    ],
                    'page_reload' => false,
                ],
                'expectedOptions' => [
                    'target_field_type' => 'target_field_type',
                    'target_field_options' => [
                        'test_1' => 'Test 1',
                        'test_2' => 'Test 2',
                    ],
                ]
            ],
            'with reserved options' => [
                'fieldsRoot' => [
                    'type' => 'target_field_type',
                    'options' => [
                        'label' => 'Label',
                        'required' => 'Required',
                        'block' => 'Block',
                        'subblock' => 'Subblock',
                        'tooltip' => 'Tooltip',
                        'resettable' => 'Resettable',
                    ],
                    'page_reload' => false,
                ],
                'expectedOptions' => [
                    'target_field_type' => 'target_field_type',
                    'target_field_options' => [
                    ],
                    'label' => 'Label',
                    'required' => 'Required',
                    'block' => 'Block',
                    'subblock' => 'Subblock',
                    'tooltip' => 'Tooltip',
                    'resettable' => 'Resettable',
                ]
            ],
            'needs page reload' => [
                'fieldsRoot' => [
                    'type' => 'target_field_type',
                    'page_reload' => true,
                ],
                'expectedOptions' => [
                    'target_field_type' => 'target_field_type',
                    'target_field_options' => [
                        'attr' => [
                            'data-needs-page-reload' => ''
                        ]
                    ],
                    'use_parent_field_options' => [
                        'attr' => [
                            'data-needs-page-reload' => ''
                        ]
                    ],
                ]
            ],
        ];
    }
}
