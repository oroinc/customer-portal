<?php

namespace Oro\Bundle\CustomerBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Represents a form type for customer user password reset
 */
class CustomerUserPasswordResetType extends AbstractType
{
    public const NAME = 'oro_customer_customer_user_password_reset';

    /**
     * @var string
     */
    protected $dataClass;

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'plainPassword',
            RepeatedType::class,
            [
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'oro.customer.customeruser.password.label',
                    'attr' => [
                        'placeholder' => 'oro.customer.customeruser.placeholder.password',
                    ]
                ],
                'second_options' => [
                    'label' => 'oro.customer.customeruser.password_confirmation.label',
                    'attr' => [
                        'placeholder' => 'oro.customer.customeruser.placeholder.password_confirmation'
                    ]
                ],
                'invalid_message' => 'oro.customer.message.password_mismatch',
                'required' => true,
                'validation_groups' => ['create']
            ]
        );
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => $this->dataClass,
            'csrf_token_id' => 'customer_user_reset',
            'dynamic_fields_disabled' => true
        ]);
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

    /**
     * @param string $dataClass
     * @return CustomerUserPasswordResetType
     */
    public function setDataClass($dataClass)
    {
        $this->dataClass = $dataClass;

        return $this;
    }
}
