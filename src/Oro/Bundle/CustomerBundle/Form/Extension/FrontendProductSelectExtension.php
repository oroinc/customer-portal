<?php

namespace Oro\Bundle\CustomerBundle\Form\Extension;

use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\ProductBundle\Form\Type\ProductSelectType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Substitutes "grid_name" option if {@see \Oro\Bundle\ProductBundle\Form\Type\ProductSelectType} form
 * is used on the storefront.
 */
class FrontendProductSelectExtension extends AbstractTypeExtension
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
            $resolver->setDefault('grid_name', 'products-select-grid-frontend');
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        return [ProductSelectType::class];
    }
}
