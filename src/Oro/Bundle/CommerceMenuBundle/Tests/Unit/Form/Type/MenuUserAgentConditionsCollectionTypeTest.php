<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Form\Type;

use Oro\Bundle\CommerceMenuBundle\Entity\MenuUserAgentCondition;
use Oro\Bundle\CommerceMenuBundle\Form\DataTransformer\MenuUserAgentConditionsCollectionTransformer;
use Oro\Bundle\CommerceMenuBundle\Form\Type\MenuUserAgentConditionsCollectionType;
use Oro\Bundle\CommerceMenuBundle\Form\Type\MenuUserAgentConditionType;
use Oro\Bundle\FormBundle\Form\Type\CollectionType as OroCollectionType;
use Oro\Component\Testing\Unit\FormIntegrationTestCase;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MenuUserAgentConditionsCollectionTypeTest extends FormIntegrationTestCase
{
    /** @var MenuUserAgentConditionType */
    private $formType;

    /** @var MenuUserAgentConditionsCollectionTransformer */
    private $transformer;

    protected function setUp(): void
    {
        $this->transformer = new MenuUserAgentConditionsCollectionTransformer();
        $this->formType = new MenuUserAgentConditionsCollectionType($this->transformer);

        parent::setUp();
    }

    /**
     * {@inheritdoc}
     */
    protected function getExtensions(): array
    {
        return [
            new PreloadedExtension(
                [
                    CollectionType::class => new CollectionType(),
                    OroCollectionType::class => new OroCollectionType(),
                    MenuUserAgentConditionType::class => new MenuUserAgentConditionType(),
                    MenuUserAgentConditionsCollectionType::class =>
                        new MenuUserAgentConditionsCollectionType($this->transformer),
                ],
                []
            ),
            $this->getValidatorExtension(true)
        ];
    }

    public function testSubmitValid()
    {
        $menuUserAgentCondition = new MenuUserAgentCondition();
        $menuUserAgentCondition
            ->setOperation('contains')
            ->setValue('sample condition')
            ->setConditionGroupIdentifier(0);

        $form = $this->factory->create(MenuUserAgentConditionsCollectionType::class, []);

        $form->submit(
            [
                $menuUserAgentCondition->getConditionGroupIdentifier() => [
                    0 => [
                        'operation' => $menuUserAgentCondition->getOperation(),
                        'value' => $menuUserAgentCondition->getValue(),
                    ],
                ],
            ]
        );

        $expected = [$menuUserAgentCondition];

        $this->assertFormIsValid($form);
        $this->assertEquals($expected, $form->getData()->toArray());
    }

    public function testBuildForm()
    {
        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects(self::once())
            ->method('addModelTransformer')
            ->with($this->transformer);

        $builder->expects(self::once())
            ->method('addEventListener')
            ->willReturnCallback(function ($eventType, $callback, $priority) {
                self::assertEquals(FormEvents::PRE_SET_DATA, $eventType);
                self::assertEquals('preSetData', $callback[1]);
                self::assertEquals(10, $priority);
            });

        $this->formType->buildForm($builder, []);
    }

    public function testGetBlockPrefix()
    {
        self::assertEquals('oro_commerce_menu_user_agent_conditions_collection', $this->formType->getBlockPrefix());
    }

    public function testConfigureOptions()
    {
        $optionsResolver = new OptionsResolver();
        $this->formType->configureOptions($optionsResolver);

        $actualOptions = $optionsResolver->resolve([]);
        $expectedOptions = [
            'entry_type' => OroCollectionType::class,
            'add_label' => 'oro.commercemenu.menu_user_agent_conditions_collection.add_label.label',
            'prototype' => true,
            'prototype_name' => '__menu_user_agent_conditions__',
            'handle_primary' => false,
            'show_form_when_empty' => false,
            'entry_options' => [
                'entry_type' => MenuUserAgentConditionType::class,
                'add_label' => 'oro.commercemenu.menu_user_agent_conditions_collection_group.add_label.label',
                'prototype' => true,
                'prototype_name' => '__menu_user_agent_conditions_group__',
                'handle_primary' => false,
                'required' => false,
            ],
        ];

        self::assertEquals($expectedOptions, $actualOptions);
    }
}
