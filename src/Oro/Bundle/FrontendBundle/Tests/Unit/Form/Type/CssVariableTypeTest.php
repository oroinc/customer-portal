<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Form\Type;

use Oro\Bundle\FrontendBundle\Form\Type\CssVariableType;
use Oro\Bundle\FrontendBundle\Model\CssVariableConfig;
use Oro\Component\Testing\Unit\FormIntegrationTestCase;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

final class CssVariableTypeTest extends FormIntegrationTestCase
{
    private CssVariableType $formType;

    #[\Override]
    protected function setUp(): void
    {
        $this->innerFormType = $this->createMock(FormTypeInterface::class);

        $this->formType = new CssVariableType();
        parent::setUp();
    }

    public function testBuildForm(): void
    {
        $form = $this->factory->create(
            $this->formType::class,
            new CssVariableConfig(),
            [
                'parentConfig' => [
                    'class' => TextType::class,
                    'constraints' => [new NotBlank()],
                    'options' => []
                ]
            ]
        );

        $valueType = $form->get('value');

        self::assertFormContainsField('value', $form);
        self::assertInstanceOf(TextType::class, $valueType->getConfig()->getType()->getInnerType());
        self::assertEquals([new NotBlank()], $valueType->getConfig()->getOption('constraints'));
    }

    public function testConfigureOptions(): void
    {
        $optionsResolver = new OptionsResolver();

        $this->formType->configureOptions($optionsResolver);

        self::assertEquals(
            [
                'data_class',
                'parentConfig',
                'cssVariableName'
            ],
            $optionsResolver->getDefinedOptions()
        );

        self::assertTrue($optionsResolver->hasDefault('data_class'));
    }

    #[\Override]
    protected function getExtensions(): array
    {
        return [
            $this->getValidatorExtension()
        ];
    }
}
