<?php

namespace Oro\Bundle\FrontendBundle\Form\Type;

use Oro\Bundle\AddressBundle\Form\Type\RegionType as BaseCountryType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class RegionType extends BaseCountryType
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
        return 'oro_frontend_region';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'translatable_entity';
    }
}
