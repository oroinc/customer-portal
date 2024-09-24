<?php

namespace Oro\Bundle\CustomerBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\OroEntitySelectOrCreateInlineType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Autocomplete select form type for enabled localizations.
 */
class EnabledLocalizationSelectType extends AbstractType
{
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'oro_customer_enabled_localization';
    }

    #[\Override]
    public function getParent(): ?string
    {
        return OroEntitySelectOrCreateInlineType::class;
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'autocomplete_alias' => 'oro_enabled_localization',
            'create_enabled' => false,
            'grid_name' => 'enabled-localizations-select-grid',
            'configs' => [
                'component' => 'autocomplete-enabledlocalization',
            ],
        ]);
    }
}
