<?php

namespace Oro\Bundle\CustomerBundle\Form\Type\Frontend;

use Oro\Bundle\CustomerBundle\Form\Type\CustomerUserMultiSelectType as BaseCustomerUserMultiSelectType;
use Oro\Bundle\CustomerBundle\Form\Type\FrontendCustomerUserType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Provides a frontend form type for selecting multiple customer users with autocomplete.
 *
 * This form type extends the backend CustomerUserMultiSelectType and customizes it for
 * frontend use with a frontend-specific autocomplete route and customer user type configuration.
 * It maintains the multi-select functionality with autocomplete component for storefront interfaces.
 */
class CustomerUserMultiSelectType extends BaseCustomerUserMultiSelectType
{
    const NAME = 'oro_customer_frontend_customer_user_multiselect';

    #[\Override]
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
