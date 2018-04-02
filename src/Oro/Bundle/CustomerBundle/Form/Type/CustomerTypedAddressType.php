<?php

namespace Oro\Bundle\CustomerBundle\Form\Type;

use Oro\Bundle\AddressBundle\Form\EventListener\FixAddressesPrimarySubscriber;
use Oro\Bundle\CustomerBundle\Form\EventListener\FixCustomerAddressesDefaultSubscriber;
use Oro\Bundle\FormBundle\Form\Extension\StripTagsExtension;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraint;

class CustomerTypedAddressType extends AbstractType
{
    const NAME = 'oro_customer_typed_address';

    /** @var string */
    protected $dataClass;

    /** @var string */
    protected $addressTypeDataClass;

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['single_form'] && $options['all_addresses_property_path']) {
            $builder->addEventSubscriber(
                new FixAddressesPrimarySubscriber($options['all_addresses_property_path'])
            );
            $builder->addEventSubscriber(
                new FixCustomerAddressesDefaultSubscriber($options['all_addresses_property_path'])
            );
        }

        $builder
            ->add(
                'phone',
                'text',
                [
                    'required' => false,
                    StripTagsExtension::OPTION_NAME => true,
                ]
            )
            ->add(
                'types',
                'translatable_entity',
                [
                    'class'    => $this->addressTypeDataClass,
                    'property' => 'label',
                    'required' => false,
                    'multiple' => true,
                    'expanded' => true
                ]
            )
            ->add(
                'defaults',
                CustomerTypedAddressWithDefaultType::NAME,
                [
                    'class'    => $this->addressTypeDataClass,
                    'required' => false,
                ]
            )
            ->add(
                'primary',
                'checkbox',
                [
                    'required' => false
                ]
            );

        $builder->get('city')->setRequired(true);
        $builder->get('postalCode')->setRequired(true);
        $builder->get('street')->setRequired(true);
        $builder->get('region')->setRequired(true);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => $this->dataClass,
                'single_form' => true,
                'all_addresses_property_path' => 'frontendOwner.addresses',
                'ownership_disabled' => true,
                'validation_groups' => [Constraint::DEFAULT_GROUP, 'RequireName', 'RequirePeriod'],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'oro_address';
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
        return static::NAME;
    }

    /**
     * @param string $dataClass
     */
    public function setDataClass($dataClass)
    {
        $this->dataClass = $dataClass;
    }

    /**
     * @param string $addressTypeDataClass
     */
    public function setAddressTypeDataClass($addressTypeDataClass)
    {
        $this->addressTypeDataClass = $addressTypeDataClass;
    }
}
