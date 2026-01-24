<?php

namespace Oro\Bundle\FrontendBundle\Form\Type;

use Oro\Bundle\AddressBundle\Form\Type\RegionType as BaseCountryType;
use Oro\Bundle\TranslationBundle\Form\Type\TranslatableEntityType;

/**
 * Provides a customized region selection form type for the storefront.
 *
 * Extends the base region type from the `AddressBundle` to provide frontend-specific
 * functionality. Uses TranslatableEntityType as the parent to support translatable
 * region entities and customizes the form block prefix for frontend integration.
 */
class RegionType extends BaseCountryType
{
    #[\Override]
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'oro_frontend_region';
    }

    #[\Override]
    public function getParent(): ?string
    {
        return TranslatableEntityType::class;
    }
}
