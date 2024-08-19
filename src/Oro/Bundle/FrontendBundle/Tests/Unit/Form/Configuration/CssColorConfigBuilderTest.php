<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Form\Configuration;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\FormBundle\Form\Type\OroSimpleColorPickerType;
use Oro\Bundle\FrontendBundle\Form\Configuration\CssColorConfigBuilder;
use Oro\Component\Testing\Unit\PreloadedExtension;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

final class CssColorConfigBuilderTest extends AbstractCssConfigBuilderTest
{
    private OroSimpleColorPickerType $simpleColorPickerType;

    private ConfigManager&MockObject $configManager;

    protected function setUp(): void
    {
        $this->configManager = $this->createMock(ConfigManager::class);

        $this->simpleColorPickerType = new OroSimpleColorPickerType(
            $this->configManager,
            $this->getTranslator()
        );

        parent::setUp();

        $packages = $this->createMock(Packages::class);
        $this->configBuilder = new CssColorConfigBuilder($packages, $this->translator);
    }

    public function testThatTypeConfigured(): void
    {
        self::assertEquals('css_color', $this->configBuilder::getType());
    }

    public function testThatAllowCustomOptionIsNotOverwritten(): void
    {
        $formBuilder = $this->factory->createBuilder();

        $this->configBuilder->buildOption(
            $formBuilder,
            [
                'name' => 'option',
                'label' => 'label',
                'options' => [
                    'parentConfig' => [
                        'options' => [
                            'allow_custom_color' => false
                        ]
                    ]
                ]
            ]
        );

        $formOptionConfig = $formBuilder->getForm()->get('option')->getConfig();

        self::assertFalse($formOptionConfig->getOption('parentConfig')['options']['allow_custom_color']);
    }

    public function testThatOptionsSet(): void
    {
        $formBuilder = $this->factory->createBuilder();

        $this->configBuilder->buildOption(
            $formBuilder,
            [
                'name' => 'option',
                'label' => 'label'
            ]
        );

        $formOptionConfig = $formBuilder->getForm()->get('option')->getConfig();

        self::assertTrue($formOptionConfig->getOption('parentConfig')['options']['allow_custom_color']);
        self::assertTrue($formOptionConfig->getOption('parentConfig')['options']['show_input_control']);
    }

    public function testThatAllowCustomOptionIsNotSetForNotSupportedFormType(): void
    {
        $formBuilder = $this->factory->createBuilder();

        $this->configBuilder->buildOption(
            $formBuilder,
            [
                'name' => 'option',
                'label' => 'label',
                'options' => [
                    'parentConfig' => [
                        'class' => TextareaType::class
                    ]
                ]
            ]
        );

        $formOptionConfig = $formBuilder->getForm()->get('option')->getConfig();

        self::assertFalse(
            isset($formOptionConfig->getOption('parentConfig')['options']['allow_custom_color'])
        );
    }

    protected function getValidValueDataProvider(): array
    {
        return [
            'short with hash' => ['#000'],
            'long with hash' => ['#000000'],
            'by word' => ['red'],
            'by func rgba' => ['rgba(0, 0, 0, .2)'],
            'by func rgb' => ['rgb(0, 0, 0)'],
            'by func hsl' => ['hsl(0, 100%, 50%)']
        ];
    }

    protected function getNotValidValueDataProvider(): array
    {
        return [
            'not exists color' => ['beautiful'],
            'broken hash' => ['#10100'],
            'broken hash 2' => ['#10 00'],
            'not allowed symbol' => ['#00-'],
        ];
    }

    protected function getSupportsDataProvider(): array
    {
        return [
            ['unknown_type', false],
            [CssColorConfigBuilder::getType(), true],
        ];
    }

    protected function getExtensions(): array
    {
        $extensions = parent::getExtensions();

        $extensions[] = new PreloadedExtension(
            [
                OroSimpleColorPickerType::class => $this->simpleColorPickerType
            ],
            []
        );

        return $extensions;
    }
}
