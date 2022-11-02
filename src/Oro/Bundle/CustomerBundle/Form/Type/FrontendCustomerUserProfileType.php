<?php

namespace Oro\Bundle\CustomerBundle\Form\Type;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\FormBundle\Form\Type\OroBirthdayType;
use Oro\Bundle\UserBundle\Form\Type\ChangePasswordType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for customer profile at storefront
 */
class FrontendCustomerUserProfileType extends AbstractType
{
    const NAME = 'oro_customer_frontend_customer_user_profile';

    /**
     * @var ConfigManager
     */
    private $configManager;

    /**
     * @var string
     */
    protected $dataClass;

    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

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
                TextType::class,
                [
                    'required' => false,
                    'label' => 'oro.customer.customeruser.name_prefix.label'
                ]
            )
            ->add(
                'firstName',
                TextType::class,
                [
                    'required' => true,
                    'label' => 'oro.customer.customeruser.first_name.label'
                ]
            )
            ->add(
                'middleName',
                TextType::class,
                [
                    'required' => false,
                    'label' => 'oro.customer.customeruser.middle_name.label'
                ]
            )
            ->add(
                'lastName',
                TextType::class,
                [
                    'required' => true,
                    'label' => 'oro.customer.customeruser.last_name.label'
                ]
            )
            ->add(
                'nameSuffix',
                TextType::class,
                [
                    'required' => false,
                    'label' => 'oro.customer.customeruser.name_suffix.label'
                ]
            )
            ->add(
                'birthday',
                OroBirthdayType::class,
                [
                    'required' => false,
                    'label' => 'oro.customer.customeruser.birthday.label'
                ]
            )
            ->add(
                'email',
                EmailType::class,
                [
                    'required' => true,
                    'label' => 'oro.customer.customeruser.email.label'
                ]
            )
            ->add(
                'changePassword',
                ChangePasswordType::class,
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
     */
    public function preSetData(FormEvent $event)
    {
        if (!$this->isCompanyNameFieldEnabled()) {
            return;
        }

        $event->getForm()->add('customer', FrontendOwnerSelectType::class, [
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
                'csrf_token_id' => 'frontend_customer_user',
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

    /**
     * @return bool
     */
    protected function isCompanyNameFieldEnabled()
    {
        return (bool) $this->configManager->get('oro_customer.company_name_field_enabled');
    }
}
