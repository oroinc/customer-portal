<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type\Stub;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class AddressTypeStub extends AbstractType
{
    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'oro_address';
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id')
            ->add('label')
            ->add('namePrefix')
            ->add('firstName')
            ->add('middleName')
            ->add('lastName')
            ->add('nameSuffix')
            ->add('organization')
            ->add('country')
            ->add('street')
            ->add('street2')
            ->add('city')
            ->add('region')
            ->add('postalCode');
    }
}
