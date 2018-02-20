<?php

namespace Oro\Bundle\CustomerBundle\Form\Type;

use Oro\Bundle\AddressBundle\Form\Type\AddressCollectionType;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerUserRoleRepository;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\UserBundle\Form\Type\UserMultiSelectType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class CustomerUserType extends AbstractType
{
    const NAME = 'oro_customer_customer_user';

    /** @var string */
    protected $dataClass;

    /** @var string */
    protected $addressClass;

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /** @var TokenAccessorInterface */
    protected $tokenAccessor;

    /**
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param TokenAccessorInterface        $tokenAccessor
     */
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        TokenAccessorInterface $tokenAccessor
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenAccessor = $tokenAccessor;
    }

    /**
     * @param string $dataClass
     */
    public function setDataClass($dataClass)
    {
        $this->dataClass = $dataClass;
    }

    /**
     * @param string $addressClass
     */
    public function setAddressClass($addressClass)
    {
        $this->addressClass = $addressClass;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->addEntityFields($builder);
        $data = $builder->getData();

        $passwordOptions = [
            'type' => 'password',
            'required' => false,
            'first_options' => [
                'label' => 'oro.customer.customeruser.password.label',
                'attr' => ['autocomplete' => 'new-password'],
            ],
            'second_options' => ['label' => 'oro.customer.customeruser.password_confirmation.label'],
            'invalid_message' => 'oro.customer.message.password_mismatch',
        ];

        if ($data instanceof CustomerUser && $data->getId()) {
            $passwordOptions = array_merge($passwordOptions, ['required' => false]);
        } else {
            $this->addNewUserFields($builder);
            $passwordOptions = array_merge($passwordOptions, ['required' => true, 'validation_groups' => ['create']]);
        }

        $builder->add('plainPassword', 'repeated', $passwordOptions);
    }

    /**
     * @param FormBuilderInterface $builder
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function addEntityFields(FormBuilderInterface $builder)
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
                'email',
                'email',
                [
                    'required' => true,
                    'label' => 'oro.customer.customeruser.email.label'
                ]
            )
            ->add(
                'customer',
                CustomerSelectType::NAME,
                [
                    'required' => true,
                    'label' => 'oro.customer.customeruser.customer.label'
                ]
            )
            ->add(
                'enabled',
                'checkbox',
                [
                    'required' => false,
                    'label' => 'oro.customer.customeruser.enabled.label',
                ]
            )
            ->add(
                'birthday',
                'oro_date',
                [
                    'required' => false,
                    'label' => 'oro.customer.customeruser.birthday.label',
                ]
            )
            ->add(
                'salesRepresentatives',
                UserMultiSelectType::NAME,
                [
                    'label' => 'oro.customer.customer.sales_representatives.label',
                ]
            )
            ->add(
                'isGuest',
                'checkbox',
                [
                    'required' => false,
                    'label' => 'oro.customer.customeruser.is_guest.label',
                ]
            );


        if ($this->authorizationChecker->isGranted('oro_customer_customer_user_role_view')) {
            $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'preSetData']);
            $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'preSubmit']);
        }

        if ($this->authorizationChecker->isGranted('oro_customer_customer_user_address_update')) {
            $options = [
                'label' => 'oro.customer.customeruser.addresses.label',
                'type' => CustomerUserTypedAddressType::NAME,
                'required' => false,
                'options' => [
                    'data_class' => $this->addressClass,
                    'single_form' => false,
                ],
            ];

            if (!$this->authorizationChecker->isGranted('oro_customer_customer_user_address_create')) {
                $options['allow_add'] = false;
            }

            if (!$this->authorizationChecker->isGranted('oro_customer_customer_user_address_remove')) {
                $options['allow_delete'] = false;
            }

            $builder
                ->add(
                    'addresses',
                    AddressCollectionType::NAME,
                    $options
                );
        }
    }

    /**
     * @param FormBuilderInterface $builder
     */
    protected function addNewUserFields(FormBuilderInterface $builder)
    {
        $builder
            ->add(
                'passwordGenerate',
                'checkbox',
                [
                    'required' => false,
                    'label' => 'oro.customer.customeruser.password_generate.label',
                    'mapped' => false
                ]
            )
            ->add(
                'sendEmail',
                'checkbox',
                [
                    'required' => false,
                    'label' => 'oro.customer.customeruser.send_email.label',
                    'mapped' => false
                ]
            );
    }

    /**
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $form = $event->getForm();

        /** @var CustomerUser $data */
        $data = $event->getData();
        $data->setOrganization($this->tokenAccessor->getOrganization());

        $form->add(
            'roles',
            CustomerUserRoleSelectType::NAME,
            [
                'query_builder' => function (CustomerUserRoleRepository $repository) use ($data) {
                    return $repository->getAvailableRolesByCustomerUserQueryBuilder(
                        $data->getOrganization(),
                        $data->getCustomer()
                    );
                }
            ]
        );
    }

    /**
     * @param FormEvent $event
     */
    public function preSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        $form->add(
            'roles',
            CustomerUserRoleSelectType::NAME,
            [
                'query_builder' => function (CustomerUserRoleRepository $repository) use ($data) {
                    $customer = null;
                    if (array_key_exists('customer', $data)) {
                        $customer = $data['customer'];
                    }

                    return $repository->getAvailableRolesByCustomerUserQueryBuilder(
                        $this->tokenAccessor->getOrganization(),
                        $customer
                    );
                }
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['data']);

        $resolver->setDefaults([
            'data_class' => $this->dataClass,
            'intention' => 'customer_user',
        ]);
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
}
