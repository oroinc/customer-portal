<?php

namespace Oro\Bundle\CustomerBundle\Form\Type;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\UserBundle\Form\Type\ChangePasswordType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for changing customer user password in storefront profile.
 */
class FrontendCustomerUserProfilePasswordType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'changePassword',
            ChangePasswordType::class,
            [
                'required' => true,
                'current_password_label' => 'oro.customer.customeruser.current_password.label',
                'plain_password_invalid_message' => 'oro.customer.message.password_mismatch',
                'first_options_label' => 'oro.customer.customeruser.new_password.label',
                'second_options_label' => 'oro.customer.customeruser.password_confirmation.label'
            ]
        );
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => CustomerUser::class,
                'csrf_token_id' => 'frontend_customer_user',
            ]
        );
    }
}
