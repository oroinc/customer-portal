<?php

namespace Oro\Bundle\CustomerBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Represents a form type for requesting a customer user password reset
 */
class CustomerUserPasswordRequestType extends AbstractType
{
    const NAME = 'oro_customer_customer_user_password_request';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'email',
            EmailType::class,
            [
                'required' => true,
                'label' => 'oro.customer.customeruser.email.label',
                'constraints' => [
                    new NotBlank(),
                    new Email(['mode' => Email::VALIDATION_MODE_STRICT])
                ],
                'attr' => [
                    'placeholder' => 'oro.customer.customeruser.placeholder.email'
                ]
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
        return self::NAME;
    }
}
