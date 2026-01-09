<?php

namespace Oro\Bundle\CustomerBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\OroEntitySelectOrCreateInlineType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Provides a form type for selecting a customer user with inline creation capability.
 *
 * This form type extends OroEntitySelectOrCreateInlineType to allow users to select an existing
 * customer user or create a new one directly from the form. It includes autocomplete functionality
 * with a custom component, a custom CSS class for styling, and a placeholder for improved UX.
 */
class CustomerUserSelectType extends AbstractType
{
    public const NAME = 'oro_customer_customer_user_select';

    #[\Override]
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'autocomplete_alias' => CustomerUserType::class,
                'create_form_route' => 'oro_customer_customer_user_create',
                'configs' => [
                    'component' => 'autocomplete-customeruser',
                    'placeholder' => 'oro.customer.customeruser.form.choose',
                ],
                'attr' => [
                    'class' => 'customer-customeruser-select',
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
