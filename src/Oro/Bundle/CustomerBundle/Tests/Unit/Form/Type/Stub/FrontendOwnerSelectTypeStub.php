<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type\Stub;

use Oro\Component\Testing\Unit\Form\Type\Stub\EntityTypeStub;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FrontendOwnerSelectTypeStub extends EntityTypeStub
{
    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'oro_customer_frontend_owner_select';
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'choice_label'  => null,
            'class'         => null,
            'targetObject'  => null,
            'query_builder' => null,
        ]);
    }
}
