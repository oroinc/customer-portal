<?php

namespace Oro\Bundle\FrontendBundle\Form\Type;

use Oro\Bundle\AddressBundle\Form\Type\CountryType as BaseCountryType;
use Oro\Bundle\TranslationBundle\Form\Type\TranslatableEntityType;

/**
 * Provides a customized country selection form type for the storefront.
 *
 * Extends the base country type from the `AddressBundle` to provide frontend-specific
 * functionality. Uses {@see TranslatableEntityType} as the parent to support translatable
 * country entities and customizes the form block prefix for frontend integration.
 */
class CountryType extends BaseCountryType
{
    #[\Override]
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'oro_frontend_country';
    }

    #[\Override]
    public function getParent(): ?string
    {
        return TranslatableEntityType::class;
    }
}
