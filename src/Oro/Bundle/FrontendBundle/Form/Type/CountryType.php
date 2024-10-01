<?php

namespace Oro\Bundle\FrontendBundle\Form\Type;

use Oro\Bundle\AddressBundle\Form\Type\CountryType as BaseCountryType;
use Oro\Bundle\TranslationBundle\Form\Type\TranslatableEntityType;

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
