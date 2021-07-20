<?php

namespace Oro\Bundle\CommerceMenuBundle\Form\Type;

use Oro\Bundle\CommerceMenuBundle\Entity\MenuUserAgentCondition;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MenuUserAgentConditionType extends AbstractType
{
    const NAME = 'oro_commerce_menu_user_agent_condition';

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('value', TextType::class, [
                'label' => 'oro.commercemenu.menu_user_agent_condition.value.label',
                'required' => true,
            ])
            ->add('operation', ChoiceType::class, [
                'label' => 'oro.commercemenu.menu_user_agent_condition.operation.label',
                'required' => true,
                'choices' => $this->getOperationChoices(),
            ]);
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => MenuUserAgentCondition::class]);
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

    /**
     * @return array
     */
    private function getOperationChoices()
    {
        return [
            'oro.commercemenu.menu_user_agent_condition.operation.contains.label' =>
                MenuUserAgentCondition::OPERATION_CONTAINS,
            'oro.commercemenu.menu_user_agent_condition.operation.does_not_contain.label' =>
                MenuUserAgentCondition::OPERATION_DOES_NOT_CONTAIN,
            'oro.commercemenu.menu_user_agent_condition.operation.matches.label' =>
                MenuUserAgentCondition::OPERATION_MATCHES,
            'oro.commercemenu.menu_user_agent_condition.operation.does_not_match.label' =>
                MenuUserAgentCondition::OPERATION_DOES_NOT_MATCHES
        ];
    }
}
