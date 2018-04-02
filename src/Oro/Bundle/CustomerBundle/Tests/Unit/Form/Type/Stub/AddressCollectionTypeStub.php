<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type\Stub;

use Oro\Bundle\AddressBundle\Form\Type\AddressCollectionType;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerTypedAddressType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddressCollectionTypeStub extends AddressCollectionType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'entry_type'     => CustomerTypedAddressType::NAME,
            'entry_options'  => ['data_class' => 'Oro\Bundle\CustomerBundle\Entity\CustomerAddress'],
            'multiple' => true,
        ]);

        parent::configureOptions($resolver);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'test_address_entity';
    }
}
