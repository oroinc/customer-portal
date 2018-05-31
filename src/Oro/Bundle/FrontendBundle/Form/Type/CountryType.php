<?php

namespace Oro\Bundle\FrontendBundle\Form\Type;

use Oro\Bundle\AddressBundle\Form\Type\CountryType as BaseCountryType;
use Oro\Bundle\TranslationBundle\Form\Type\TranslatableEntityType;

class CountryType extends BaseCountryType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'oro_frontend_country';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return TranslatableEntityType::class;
    }
}
