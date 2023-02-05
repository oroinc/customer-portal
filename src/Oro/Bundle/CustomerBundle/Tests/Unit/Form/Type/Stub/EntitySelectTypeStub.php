<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type\Stub;

use Oro\Component\Testing\Unit\Form\Type\Stub\EntityTypeStub;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntitySelectTypeStub extends EntityTypeStub
{
    private ?AbstractType $formType;

    public function __construct(array $choices, ?AbstractType $formType = null)
    {
        parent::__construct($choices);
        $this->formType = $formType;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $this->formType?->configureOptions($resolver);
    }
}
