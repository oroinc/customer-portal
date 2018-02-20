<?php

namespace Oro\Bundle\CustomerBundle\Form\Type;

use Oro\Bundle\AddressBundle\Validator\Constraints\NameOrOrganization;
use Oro\Bundle\CustomerBundle\Entity\AbstractDefaultTypedAddress;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FrontendCustomerTypedAddressType extends CustomerTypedAddressType
{
    const NAME = 'oro_customer_frontend_typed_address';

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'constraints' => [new NameOrOrganization()],
            ]
        );

        parent::configureOptions($resolver);
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'preSetData']);
    }

    /**
     * PRE_SET_DATA event handler
     *
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $form = $event->getForm();
        $address = $event->getData();

        $form->add('frontendOwner', FrontendOwnerSelectType::NAME, [
            'label' => 'oro.customer.customer.entity_label',
            'targetObject' => $address,
        ]);

        if (is_a($address, AbstractDefaultTypedAddress::class)
            && $form->has('primary')
            && $this->isHidePrimaryAddress($address)
        ) {
            $form->remove('primary');
        }
    }

    /**
     * @param AbstractDefaultTypedAddress $address
     * @return bool
     */
    protected function isHidePrimaryAddress($address)
    {
        return count($address->getFrontendOwner()->getAddresses()) <= 1;
    }
}
