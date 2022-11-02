<?php

namespace Oro\Bundle\CustomerBundle\Form\Type;

use Oro\Bundle\CustomerBundle\Entity\AbstractDefaultTypedAddress;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Allows to create customer address entity on front store and mark this address as shipping or billing type
 */
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
                'owner_field_label' => 'oro.customer.frontend.customer.entity_label'
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
     */
    public function preSetData(FormEvent $event)
    {
        $form = $event->getForm();
        $address = $event->getData();

        $form->add('frontendOwner', FrontendOwnerSelectType::class, [
            'label' => $form->getConfig()->getOption('owner_field_label'),
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
