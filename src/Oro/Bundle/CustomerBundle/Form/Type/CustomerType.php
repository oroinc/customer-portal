<?php

namespace Oro\Bundle\CustomerBundle\Form\Type;

use Oro\Bundle\AddressBundle\Form\Type\AddressCollectionType;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Event\CustomerEvent;
use Oro\Bundle\EntityExtendBundle\Form\Type\EnumSelectType;
use Oro\Bundle\UserBundle\Form\Type\UserMultiSelectType;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Manage Customer from
 */
class CustomerType extends AbstractType
{
    const NAME = 'oro_customer_type';
    const GROUP_FIELD = 'group';

    /**
     * @var string
     */
    protected $addressClass;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var array
     */
    protected $modelChangeSet = [];

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, ['label' => 'oro.customer.customer.name.label'])
            ->add(
                self::GROUP_FIELD,
                CustomerGroupSelectType::class,
                [
                    'label' => 'oro.customer.customer.group.label',
                    'required' => false
                ]
            )
            ->add(
                'parent',
                ParentCustomerSelectType::class,
                [
                    'label' => 'oro.customer.customer.parent.label',
                    'required' => false
                ]
            )
            ->add(
                'internal_rating',
                EnumSelectType::class,
                [
                    'label' => 'oro.customer.customer.internal_rating.label',
                    'enum_code' => Customer::INTERNAL_RATING_CODE,
                    'configs' => [
                        'allowClear' => false,
                    ],
                    'required' => false
                ]
            )
            ->add(
                'salesRepresentatives',
                UserMultiSelectType::class,
                [
                    'label' => 'oro.customer.customer.sales_representatives.label',
                ]
            )
            ->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'preSubmit'])
            ->addEventListener(FormEvents::POST_SUBMIT, [$this, 'postSubmit']);

        if ($this->authorizationChecker->isGranted('oro_customer_customer_address_update')) {
            $options = [
                'label' => 'oro.customer.customer.addresses.label',
                'entry_type' => CustomerTypedAddressType::class,
                'required' => true,
                'entry_options' => [
                    'data_class' => $this->addressClass,
                    'single_form' => false
                ]
            ];

            if (!$this->authorizationChecker->isGranted('oro_customer_customer_address_create')) {
                $options['allow_add'] = false;
            }

            if (!$this->authorizationChecker->isGranted('oro_customer_customer_address_remove')) {
                $options['allow_delete'] = false;
            }

            $builder
                ->add(
                    'addresses',
                    AddressCollectionType::class,
                    $options
                );
        }
    }

    public function preSubmit(FormEvent $event)
    {
        $this->modelChangeSet = [];

        /** @var Customer $customer */
        $customer = $event->getForm()->getData();
        if ($customer instanceof Customer
            && $this->isCustomerGroupChanged($customer, (int)$event->getData()[self::GROUP_FIELD])
        ) {
            $this->modelChangeSet[] = self::GROUP_FIELD;
        }
    }

    /**
     * @param Customer $customer
     * @param int $newGroupId
     * @return bool
     */
    private function isCustomerGroupChanged(Customer $customer, $newGroupId)
    {
        return $customer->getGroup() && $newGroupId !== $customer->getGroup()->getId();
    }

    public function postSubmit(FormEvent $event)
    {
        /** @var Customer $customer */
        $customer = $event->getForm()->getData();
        if ($customer instanceof Customer
            && in_array(self::GROUP_FIELD, $this->modelChangeSet, true)
            && $event->getForm()->isValid()
        ) {
            $this->eventDispatcher->dispatch(
                new CustomerEvent($customer),
                CustomerEvent::ON_CUSTOMER_GROUP_CHANGE
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'csrf_token_id' => 'customer'
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
     * @param string $addressClass
     */
    public function setAddressClass($addressClass)
    {
        $this->addressClass = $addressClass;
    }
}
