<?php

namespace Oro\Bundle\CustomerBundle\Form\Type;

use Oro\Bundle\AddressBundle\Form\EventListener\FixAddressesPrimarySubscriber;
use Oro\Bundle\AddressBundle\Form\Type\AddressType;
use Oro\Bundle\AddressValidationBundle\Form\Type\AddressValidatedAtType;
use Oro\Bundle\CustomerBundle\Form\EventListener\FixCustomerAddressesDefaultSubscriber;
use Oro\Bundle\FormBundle\Form\Extension\StripTagsExtension;
use Oro\Bundle\TranslationBundle\Form\Type\TranslatableEntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Manage Customer Typed Address from
 */
class CustomerTypedAddressType extends AbstractType
{
    public const NAME = 'oro_customer_typed_address';

    /** @var string */
    protected $dataClass;

    /** @var string */
    protected $addressTypeDataClass;

    #[\Override]
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
                TextType::class,
                [
                    'required' => false,
                    StripTagsExtension::OPTION_NAME => true,
                ]
            )
            ->add(
                'types',
                TranslatableEntityType::class,
                [
                    'class' => $this->addressTypeDataClass,
                    'choice_label' => 'label',
                    'required' => false,
                    'multiple' => true,
                    'expanded' => true
                ]
            )
            ->add(
                'defaults',
                CustomerTypedAddressWithDefaultType::class,
                [
                    'class'    => $this->addressTypeDataClass,
                    'required' => false,
                ]
            )
            ->add(
                'primary',
                CheckboxType::class,
                [
                    'required' => false
                ]
            )
            ->add('validatedAt', AddressValidatedAtType::class);

        $builder->get('city')->setRequired(true);
        $builder->get('postalCode')->setRequired(true);
        $builder->get('street')->setRequired(true);
        $builder->get('region')->setRequired(true);
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => $this->dataClass,
                'single_form' => true,
                'all_addresses_property_path' => 'frontendOwner.addresses',
                'ownership_disabled' => true
            ]
        );
    }

    #[\Override]
    public function getParent(): ?string
    {
        return AddressType::class;
    }

    public function getName()
    {
        return $this->getBlockPrefix();
    }

    #[\Override]
    public function getBlockPrefix(): string
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
