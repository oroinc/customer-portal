<?php

namespace Oro\Bundle\CustomerBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\OroEntitySelectOrCreateInlineType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Provides a form type for selecting a customer group with inline creation capability.
 *
 * This form type extends OroEntitySelectOrCreateInlineType to allow users to select an existing
 * customer group or create a new one directly from the form. It is configured with autocomplete
 * functionality and a placeholder for better user experience.
 */
class CustomerGroupSelectType extends AbstractType
{
    public const NAME = 'oro_customer_customer_group_select';

    #[\Override]
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'autocomplete_alias' => 'oro_customer_group',
                'create_form_route' => 'oro_customer_customer_group_create',
                'configs' => [
                    'placeholder' => 'oro.customer.customergroup.form.choose'
                ]
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
