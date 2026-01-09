<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Unit\Form\Type\Stub;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StubType extends TextType
{
    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'ownership_disabled' => 'true',
            'website' => null,
        ]);
    }
}
