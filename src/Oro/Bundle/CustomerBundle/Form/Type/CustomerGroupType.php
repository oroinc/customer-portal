<?php

namespace Oro\Bundle\CustomerBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\EntityIdentifierType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Builds a form for creating and editing customer groups.
 *
 * This form type provides fields for managing customer group properties including the group name
 * and the ability to append or remove customers from the group. It uses EntityIdentifierType for
 * handling customer associations and is configured with a specific data class for the customer group entity.
 */
class CustomerGroupType extends AbstractType
{
    public const NAME = 'oro_customer_group_type';

    /**
     * @var string
     */
    protected $dataClass;

    /**
     * @var string
     */
    protected $customerClass;

    /**
     * @param string $dataClass
     */
    public function setDataClass($dataClass)
    {
        $this->dataClass = $dataClass;
    }

    /**
     * @param string $customerClass
     */
    public function setCustomerClass($customerClass)
    {
        $this->customerClass = $customerClass;
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                TextType::class,
                [
                    'label' => 'oro.customer.customergroup.name.label',
                    'required' => true
                ]
            )
            ->add(
                'appendCustomers',
                EntityIdentifierType::class,
                [
                    'class'    => $this->customerClass,
                    'required' => false,
                    'mapped'   => false,
                    'multiple' => true
                ]
            )
            ->add(
                'removeCustomers',
                EntityIdentifierType::class,
                [
                    'class'    => $this->customerClass,
                    'required' => false,
                    'mapped'   => false,
                    'multiple' => true
                ]
            );
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => $this->dataClass]);
    }

    public function getName()
    {
        return $this->getBlockPrefix();
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return self::NAME;
    }
}
