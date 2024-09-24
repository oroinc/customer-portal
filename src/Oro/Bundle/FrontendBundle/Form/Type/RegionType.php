<?php

namespace Oro\Bundle\FrontendBundle\Form\Type;

use Oro\Bundle\AddressBundle\Form\Type\RegionType as BaseCountryType;
use Oro\Bundle\TranslationBundle\Form\Type\TranslatableEntityType;

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
