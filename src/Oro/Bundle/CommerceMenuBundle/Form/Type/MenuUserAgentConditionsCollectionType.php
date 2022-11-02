<?php

namespace Oro\Bundle\CommerceMenuBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\CollectionType as OroCollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MenuUserAgentConditionsCollectionType extends AbstractType
{
    const NAME = 'oro_commerce_menu_user_agent_conditions_collection';

    /**
     * @var DataTransformerInterface
     */
    private $menuUserAgentConditionsTransformer;

    public function __construct(DataTransformerInterface $menuUserAgentConditionsTransformer)
    {
        $this->menuUserAgentConditionsTransformer = $menuUserAgentConditionsTransformer;
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer($this->menuUserAgentConditionsTransformer);

        // We have to transform data before
        // Symfony\Component\Form\Extension\Core\EventListener\ResizeFormListener::preSetData() is executed,
        // because regular data transformers are called after the PRE_SET_DATA event is fired.
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'preSetData'], 10);
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
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
            ]
        );
    }

    public function preSetData(FormEvent $event)
    {
        $data = $event->getData();

        $groupedConditionsArray = $this->menuUserAgentConditionsTransformer->transform($data);

        $event->setData($groupedConditionsArray);
    }

    /**
     * {@inheritDoc}
     */
    public function getParent()
    {
        return OroCollectionType::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritDoc}
     */
    public function getBlockPrefix()
    {
        return static::NAME;
    }
}
