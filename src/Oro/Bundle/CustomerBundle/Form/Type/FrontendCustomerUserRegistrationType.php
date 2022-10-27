<?php

namespace Oro\Bundle\CustomerBundle\Form\Type;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * The registration form for storefront.
 */
class FrontendCustomerUserRegistrationType extends AbstractType
{
    const NAME = 'oro_customer_frontend_customer_user_register';

    /** @var ConfigManager */
    private $configManager;

    /** @var ManagerRegistry */
    private $doctrine;

    /** @var string */
    private $dataClass;

    public function __construct(ConfigManager $configManager, ManagerRegistry $doctrine)
    {
        $this->configManager = $configManager;
        $this->doctrine = $doctrine;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($this->isCompanyNameFieldEnabled()) {
            $builder->add(
                'companyName',
                TextType::class,
                [
                    'required' => true,
                    'mapped' => false,
                    'label' => 'oro.customer.customeruser.profile.company_name',
                    'constraints' => [
                        new Assert\NotBlank(),
                        new Assert\Length(['max' => 255])
                    ],
                    'attr' => ['placeholder' => 'oro.customer.customeruser.placeholder.company_name']
                ]
            );
        }

        $builder
            ->add(
                'firstName',
                TextType::class,
                [
                    'required' => true,
                    'label' => 'oro.customer.customeruser.first_name.label',
                    'attr' => ['placeholder' => 'oro.customer.customeruser.placeholder.first_name']
                ]
            )
            ->add(
                'lastName',
                TextType::class,
                [
                    'required' => true,
                    'label' => 'oro.customer.customeruser.last_name.label',
                    'attr' => ['placeholder' => 'oro.customer.customeruser.placeholder.last_name']
                ]
            )
            ->add(
                'email',
                EmailType::class,
                [
                    'required' => true,
                    'label' => 'oro.customer.customeruser.email.label',
                    'attr' => ['placeholder' => 'oro.customer.customeruser.placeholder.email']
                ]
            );

        $builder->add(
            'plainPassword',
            RepeatedType::class,
            [
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'oro.customer.customeruser.password.label',
                    'attr' => [
                        'placeholder' => 'oro.customer.customeruser.placeholder.password',
                        'autocomplete' => 'new-password',
                    ],
                ],
                'second_options' => [
                    'label' => 'oro.customer.customeruser.password_confirmation.label',
                    'attr' => ['placeholder' => 'oro.customer.customeruser.placeholder.password_confirmation']
                ],
                'invalid_message' => 'oro.customer.message.password_mismatch',
                'required' => true,
                'validation_groups' => ['create']
            ]
        );

        $this->addBuilderListeners($builder);
    }

    private function addBuilderListeners(FormBuilderInterface $builder)
    {
        $builder->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $event) {
                /** @var CustomerUser $customerUser */
                $customerUser = $event->getData();

                if (!$customerUser->getOwner()) {
                    $userId = $this->configManager->get('oro_customer.default_customer_owner');

                    /** @var User $user */
                    $user = $this->doctrine->getManagerForClass(User::class)->find(User::class, $userId);

                    if ($user) {
                        $customerUser->setOwner($user);
                    }
                }
            }
        );

        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) {
                /** @var CustomerUser $customerUser */
                $customerUser = $event->getData();
                if (!$customerUser->getCustomer()) {
                    $form = $event->getForm();

                    if ($form->has('companyName')) {
                        $companyName = $form->get('companyName')->getData();
                    } else {
                        $companyName = sprintf(
                            '%s %s',
                            $form->get('firstName')->getData(),
                            $form->get('lastName')->getData()
                        );
                    }

                    $customerUser->createCustomer($companyName);
                }
            },
            10
        );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => $this->dataClass,
                'csrf_token_id' => 'customer_user'
            ]
        );
    }

    /**
     * @return string
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
     * @param string $dataClass
     * @return FrontendCustomerUserType
     */
    public function setDataClass($dataClass)
    {
        $this->dataClass = $dataClass;

        return $this;
    }

    /**
     * @return bool
     */
    private function isCompanyNameFieldEnabled()
    {
        return (bool) $this->configManager->get('oro_customer.company_name_field_enabled');
    }
}
