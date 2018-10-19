<?php

namespace Oro\Bundle\CustomerBundle\Form\Type\Frontend;

use Oro\Bundle\CustomerBundle\Form\Type\CustomerUserMultiSelectType as BaseCustomerUserMultiSelectType;
use Oro\Bundle\CustomerBundle\Form\Type\FrontendCustomerUserType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerUserMultiSelectType extends BaseCustomerUserMultiSelectType
{
    const NAME = 'oro_customer_frontend_customer_user_multiselect';

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'autocomplete_alias' => FrontendCustomerUserType::class,
                'configs' => [
                    'route_name' => 'oro_frontend_autocomplete_search',
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
}
