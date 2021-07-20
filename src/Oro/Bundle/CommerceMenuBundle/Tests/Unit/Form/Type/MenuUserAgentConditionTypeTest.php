<?php

namespace Oro\Bundle\CustomerMenuBundle\Tests\Unit\Form\Type;

use Oro\Bundle\CommerceMenuBundle\Entity\MenuUserAgentCondition;
use Oro\Bundle\CommerceMenuBundle\Form\Type\MenuUserAgentConditionType;
use Oro\Component\Testing\Unit\FormIntegrationTestCase;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MenuUserAgentConditionTypeTest extends FormIntegrationTestCase
{
    /**
     * @var MenuUserAgentConditionType
     */
    private $formType;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->formType = new MenuUserAgentConditionType();
    }

    /**
     * {@inheritDoc}
     */
    protected function getExtensions()
    {
        return [
            new PreloadedExtension([], []),
            $this->getValidatorExtension(true),
        ];
    }

    public function testSubmitIsValid()
    {
        $operation = 'matches';
        $conditionValue = 'sample condition';
        $menuUserAgentCondition = new MenuUserAgentCondition();
        $form = $this->factory->create(MenuUserAgentConditionType::class, $menuUserAgentCondition);

        $form->submit(
            [
                'operation' => $operation,
                'value' => $conditionValue,
            ]
        );

        $expectedMenuUserAgentCondition = new MenuUserAgentCondition();
        $expectedMenuUserAgentCondition
            ->setOperation($operation)
            ->setValue($conditionValue);

        $this->assertFormIsValid($form);
        $this->assertEquals($expectedMenuUserAgentCondition, $form->getData());
    }

    public function testGetBlockPrefix()
    {
        static::assertEquals('oro_commerce_menu_user_agent_condition', $this->formType->getBlockPrefix());
    }

    public function testConfigureOptions()
    {
        $optionsResolver = new OptionsResolver();
        $this->formType->configureOptions($optionsResolver);

        $actualOptions = $optionsResolver->resolve([]);
        $expectedOptions = ['data_class' => MenuUserAgentCondition::class];

        static::assertEquals($expectedOptions, $actualOptions);
    }
}
