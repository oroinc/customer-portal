<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type\Stub;

use Oro\Bundle\AddressBundle\Form\Type\AddressCollectionType;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerTypedAddressType;
use Oro\Component\Testing\Unit\Form\Type\Stub\EntityTypeStub;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddressCollectionTypeStub extends AddressCollectionType
{
    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'entry_type' => CustomerTypedAddressType::class,
            'entry_options' => ['data_class' => CustomerAddress::class],
            'multiple' => true,
        ]);
        parent::configureOptions($resolver);
    }

    /**
     * {@inheritDoc}
     */
    public function getParent(): ?string
    {
        return EntityTypeStub::class;
    }
}
