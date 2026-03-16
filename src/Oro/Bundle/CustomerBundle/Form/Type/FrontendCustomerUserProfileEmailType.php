<?php

namespace Oro\Bundle\CustomerBundle\Form\Type;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

/**
 * Form type for changing customer user email in storefront profile.
 */
class FrontendCustomerUserProfileEmailType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'currentPassword',
                PasswordType::class,
                [
                    'required' => true,
                    'label' => 'oro.customer.customeruser.current_password.label',
                    'constraints' => [
                        new UserPassword()
                    ],
                    'mapped' => false,
                ]
            )
            ->add(
                'email',
                EmailType::class,
                [
                    'required' => true,
                    'label' => 'oro.customer.customeruser.email.label_short'
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
