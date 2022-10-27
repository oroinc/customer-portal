<?php

namespace Oro\Bundle\CustomerBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\OroEntitySelectOrCreateInlineType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for CustomerUserROle entity with inline create & select buttons
 */
class CustomerUserRoleSelectOrCreateType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'autocomplete_alias' => 'customer_user_role_select_or_create_type',
                'create_form_route' => 'oro_customer_customer_user_role_create',
                'grid_name' => 'predefined-customer-customer-user-roles-grid'
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return OroEntitySelectOrCreateInlineType::class;
    }
}
