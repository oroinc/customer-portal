<?php

namespace Oro\Bundle\CustomerBundle\Form\Type;

use Oro\Bundle\UserBundle\Form\Type\UserMultiSelectType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerUserMultiSelectType extends AbstractType
{
    const NAME = 'oro_customer_customer_user_multiselect';

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return UserMultiSelectType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'autocomplete_alias' => CustomerUserType::class,
                'configs' => [
                    'multiple' => true,
                    'component' => 'autocomplete-customeruser',
                    'placeholder' => 'oro.customer.customeruser.form.choose',
                ],
                'attr' => [
                    'class' => 'customer-customeruser-multiselect',
                ],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return static::NAME;
    }
}
