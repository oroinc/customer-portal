<?php

namespace Oro\Bundle\CustomerBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolver;

class FrontendCustomerUserTypedAddressType extends FrontendCustomerTypedAddressType
{
    const NAME = 'oro_customer_frontend_customer_user_typed_address';

    /**
     * {@inheritdoce}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(
            [
                'owner_field_label' => 'oro.customer.frontend.customer_user.entity_label'
            ]
        );
    }
}
