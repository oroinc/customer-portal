<?php

namespace Oro\Bundle\CustomerBundle\Form\Extension;

use Oro\Bundle\AddressBundle\Form\Type\AddressType;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Substitutes "region_route" option if {@see \Oro\Bundle\AddressBundle\Form\Type\AddressType} form
 * is used on the storefront.
 */
class AddressExtension extends AbstractTypeExtension
{
    /** @var FrontendHelper */
    private $frontendHelper;

    public function __construct(FrontendHelper $frontendHelper)
    {
        $this->frontendHelper = $frontendHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        if ($this->frontendHelper->isFrontendRequest()) {
            $resolver->setDefault('region_route', 'oro_api_frontend_country_get_regions');
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        return [AddressType::class];
    }
}
