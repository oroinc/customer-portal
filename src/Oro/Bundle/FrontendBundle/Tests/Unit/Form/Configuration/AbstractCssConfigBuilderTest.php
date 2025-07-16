<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Form\Configuration;

use Oro\Bundle\FrontendBundle\Form\Configuration\AbstractCssConfigBuilder;
use Oro\Bundle\FrontendBundle\Model\CssVariableConfig;
use Oro\Component\Testing\Unit\FormIntegrationTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractCssConfigBuilderTest extends FormIntegrationTestCase
{
    protected AbstractCssConfigBuilder $configBuilder;
    protected TranslatorInterface&MockObject $translator;

    #[\Override]
    protected function setUp(): void
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->translator->expects(self::any())
            ->method('trans')
            ->willReturn('Invalid message');

        parent::setUp();
    }

    /**
     * @dataProvider getSupportsDataProvider
     */
    public function testSupports(string $type, bool $expectedResult): void
    {
        self::assertEquals(
            $expectedResult,
            $this->configBuilder->supports(['type' => $type])
        );
    }

    /**
     * @dataProvider getValidValueDataProvider
     */
    public function testThatFormSubmitted(string $value): void
    {
        $formBuilder = $this->factory->createBuilder();

        $this->configBuilder->buildOption(
            $formBuilder,
            [
                'name' => 'section__option',
                'label' => 'label',
            ]
        );

        $form = $formBuilder->getForm();

        $form->submit(['section__option' => ['value' => $value]]);

        $submittedData = $form->getData();

        $cssVariableConfig = new CssVariableConfig();
        $cssVariableConfig->setValue($value);
        $cssVariableConfig->setVariableName('option');

        self::assertTrue($form->isValid());
        self::assertEquals($submittedData['section__option'], $cssVariableConfig);
    }

    /**
     * @dataProvider getValidValueDataProvider
     */
    public function testThatDefaultValueApplied(string $value): void
    {
        $formBuilder = $this->factory->createBuilder();

        $this->configBuilder->buildOption(
            $formBuilder,
            [
                'name' => 'section__option',
                'label' => 'label',
                'default' => $value
            ]
        );

        $form = $formBuilder->getForm();

        /**
         * @var CssVariableConfig $cssVariableConfig
         */
        $cssVariableConfig = $form->get('section__option')->getData();

        self::assertEquals($value, $cssVariableConfig->getValue());
    }

    /**
     * @dataProvider getNotValidValueDataProvider
     */
    public function testThatFormSubmittedWithNotValidValue(string $value): void
    {
        $formBuilder = $this->factory->createBuilder();

        $this->configBuilder->buildOption(
            $formBuilder,
            [
                'name' => 'section__option',
                'label' => 'label',
            ]
        );

        $form = $formBuilder->getForm();

        $form->submit(['section__option' => ['value' => $value]]);

        self::assertFalse($form->isValid());
    }

    /**
     * @dataProvider getValidValueDataProvider
     */
    public function testThatFormSubmittedWithCustomThemeVariableName(string $value): void
    {
        $formBuilder = $this->factory->createBuilder();

        $this->configBuilder->buildOption(
            $formBuilder,
            [
                'name' => 'section__option',
                'label' => 'label',
                'options' => [
                    'cssVariableName' => 'custom_variable_name',
                ]
            ]
        );

        $form = $formBuilder->getForm();

        $form->submit(['section__option' => ['value' => $value]]);

        $submittedData = $form->getData();

        $cssVariableConfig = new CssVariableConfig();
        $cssVariableConfig->setValue($value);
        $cssVariableConfig->setVariableName('custom_variable_name');

        self::assertTrue($form->isValid());
        self::assertEquals($submittedData['section__option'], $cssVariableConfig);
    }

    public function testThatFormSubmittedWithCustomThemeConstraints(): void
    {
        $formBuilder = $this->factory->createBuilder();

        $this->configBuilder->buildOption(
            $formBuilder,
            [
                'name' => 'section__option',
                'label' => 'label',
                'options' => [
                    'constraints' => [new NotBlank()],
                ]
            ]
        );

        $form = $formBuilder->getForm();

        $form->submit(['section__option' => ['value' => '']]);
        self::assertFalse($form->isValid());
    }

    public function testOverrideWithEmptyThemeConstraints(): void
    {
        $formBuilder = $this->factory->createBuilder();

        $this->configBuilder->buildOption(
            $formBuilder,
            [
                'name' => 'section__option',
                'label' => 'label',
                'options' => [
                    'constraints' => [],
                ]
            ]
        );

        $form = $formBuilder->getForm();

        $form->submit(['section__option' => ['value' => '$%5^^@&@*']]);
        self::assertTrue($form->isValid());
    }

    public function testThatOptionConfiguredProperly(): void
    {
        $formBuilder = $this->factory->createBuilder();

        $this->configBuilder->buildOption(
            $formBuilder,
            [
                'name' => 'section__option',
                'label' => 'label',
                'options' => [
                    'parentConfig' => [
                        'class' => TextareaType::class,
                    ],
                    'constraints' => []
                ]
            ]
        );

        $form = $formBuilder->getForm();

        $innerType = $form
            ->get('section__option')
            ->get('value')
            ->getConfig()
            ->getType()
            ->getInnerType();

        self::assertEquals(TextareaType::class, $innerType::class);
    }

    /**
     * @dataProvider cssInjectionsDataProvider
     */
    public function testOnCssInjections(string $cssInjectionValue): void
    {
        $formBuilder = $this->factory->createBuilder();

        $this->configBuilder->buildOption(
            $formBuilder,
            [
                'name' => 'section__option',
                'label' => 'label'
            ]
        );

        $form = $formBuilder->getForm();

        $form->submit(['section__option' => ['value' => $cssInjectionValue]]);
        self::assertFalse($form->isValid());
    }

    public function testThatFormAllowsOverrideParentFormType(): void
    {
        $formBuilder = $this->factory->createBuilder();

        $this->configBuilder->setParentFormType(TextareaType::class);

        $this->configBuilder->buildOption(
            $formBuilder,
            [
                'name' => 'section__option',
                'label' => 'label',
            ]
        );

        $form = $formBuilder->getForm();

        $innerType = $form
            ->get('section__option')
            ->get('value')
            ->getConfig()
            ->getType()
            ->getInnerType();

        self::assertEquals(TextareaType::class, $innerType::class);
    }

    #[\Override]
    protected function getExtensions(): array
    {
        return [
            $this->getValidatorExtension(),
        ];
    }

    protected function getSupportsDataProvider(): array
    {
        return [];
    }

    protected function getValidValueDataProvider(): array
    {
        return [
            'valid alpha numeric with space symbol' => ['20px 30px'],
        ];
    }

    protected function getNotValidValueDataProvider(): array
    {
        return [
            'not valid characters' => ['$test#']
        ];
    }

    private function cssInjectionsDataProvider(): array
    {
        $values = [];

        $fileReader = new \SplFileObject(__DIR__ . '/css_injections.csv');

        $fileReader->setFlags(\SplFileObject::READ_CSV);

        foreach ($fileReader as $row) {
            if (!$row[0]) {
                continue;
            }

            $values[$row[0]] = [$row[0]];
        }

        return $values;
    }
}
