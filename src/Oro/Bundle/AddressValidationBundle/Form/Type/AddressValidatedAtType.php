<?php

declare(strict_types=1);

namespace Oro\Bundle\AddressValidationBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

/**
 * Represents a "validatedAt" form field used in the address form.
 */
class AddressValidatedAtType extends AbstractType
{
    public function __construct(private readonly DataTransformerInterface $dataTransformer)
    {
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addViewTransformer($this->dataTransformer);
    }

    #[\Override]
    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        array_splice(
            $view->vars['block_prefixes'],
            -1,
            0,
            $form->getRoot()->getName() . '__' . $this->getBlockPrefix()
        );
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'oro_address_validation_validated_at';
    }

    #[\Override]
    public function getParent(): string
    {
        return HiddenType::class;
    }
}
