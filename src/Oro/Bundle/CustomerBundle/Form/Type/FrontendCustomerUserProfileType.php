<?php

namespace Oro\Bundle\CustomerBundle\Form\Type;

use Oro\Bundle\UserBundle\Form\Type\ChangePasswordType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FrontendCustomerUserProfileType extends AbstractType
{
    const NAME = 'oro_customer_frontend_customer_user_profile';

    /**
     * @var string
     */
    protected $dataClass;

    /**
     * @param string $dataClass
     */
    public function setDataClass($dataClass)
    {
        $this->dataClass = $dataClass;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'namePrefix',
                'text',
                [
                    'required' => false,
                    'label' => 'oro.customer.customeruser.name_prefix.label'
                ]
            )
            ->add(
                'firstName',
                'text',
                [
                    'required' => true,
                    'label' => 'oro.customer.customeruser.first_name.label'
                ]
            )
            ->add(
                'middleName',
                'text',
                [
                    'required' => false,
                    'label' => 'oro.customer.customeruser.middle_name.label'
                ]
            )
            ->add(
                'lastName',
                'text',
                [
                    'required' => true,
                    'label' => 'oro.customer.customeruser.last_name.label'
                ]
            )
            ->add(
                'nameSuffix',
                'text',
                [
                    'required' => false,
                    'label' => 'oro.customer.customeruser.name_suffix.label'
                ]
            )
            ->add(
                'birthday',
                'oro_date',
                [
                    'required' => false,
                    'label' => 'oro.customer.customeruser.birthday.label'
                ]
            )
            ->add(
                'email',
                'email',
                [
                    'required' => true,
                    'label' => 'oro.customer.customeruser.email.label'
                ]
            )
            ->add(
                'changePassword',
                ChangePasswordType::NAME,
                [
                    'current_password_label' => 'oro.customer.customeruser.current_password.label',
                    'plain_password_invalid_message' => 'oro.customer.message.password_mismatch',
                    'first_options_label' => 'oro.customer.customeruser.new_password.label',
                    'second_options_label' => 'oro.customer.customeruser.password_confirmation.label'
                ]
            );
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'preSetData']);
    }

    /**
     * PRE_SET_DATA event handler
     *
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $event->getForm()->add('customer', FrontendOwnerSelectType::NAME, [
            'label' => 'oro.customer.customer.entity_label',
            'targetObject' => $event->getData()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => $this->dataClass,
                'intention' => 'frontend_customer_user',
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
