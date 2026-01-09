<?php

namespace Oro\Bundle\CustomerBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\OroEntitySelectOrCreateInlineType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Provides a form type for selecting a customer with inline creation capability.
 *
 * This form type extends OroEntitySelectOrCreateInlineType to allow users to select an existing
 * customer or create a new one directly from the form. It includes autocomplete functionality,
 * a custom CSS class for styling, and a placeholder for improved user experience.
 */
class CustomerSelectType extends AbstractType
{
    public const NAME = 'oro_customer_customer_select';

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

    public function getName()
    {
        return $this->getBlockPrefix();
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return self::NAME;
    }

    #[\Override]
    public function getParent(): ?string
    {
        return OroEntitySelectOrCreateInlineType::class;
    }
}
