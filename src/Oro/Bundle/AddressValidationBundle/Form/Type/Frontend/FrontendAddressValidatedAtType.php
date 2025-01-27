<?php

namespace Oro\Bundle\AddressValidationBundle\Form\Type\Frontend;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Represents a "validatedAt" form field used in the address form on storefront.
 */
class FrontendAddressValidatedAtType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new DateTimeToStringTransformer());
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'oro_address_validation_frontend_validated_at';
    }

    #[\Override]
    public function getParent(): string
    {
        return HiddenType::class;
    }
}
