<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Extension\Stub;

use Oro\Bundle\CustomerBundle\Form\Type\CustomerSelectType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerSelectTypeStub extends AbstractType
{
    #[\Override]
    public function getBlockPrefix(): string
    {
        return CustomerSelectType::NAME;
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'autocomplete_alias' => 'oro_customer_customer',
                'create_form_route' => 'oro_customer_customer_create',
                'configs' => [
                    'placeholder' => 'oro.customer.customer.form.choose',
                ],
                'attr' => [
                    'class' => 'customer-customer-select',
                ],
            ]
        );
    }

    #[\Override]
    public function getParent(): ?string
    {
        return ChoiceType::class;
    }
}
