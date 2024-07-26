<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\OroIconType;
use Oro\Bundle\FrontendBundle\Provider\StorefrontIconsMappingProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for choosing a storefront icon by it fa-icon representation.
 */
class StorefrontIconType extends AbstractType
{
    public function __construct(private StorefrontIconsMappingProvider $storefrontIconsMappingProvider)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $icons = $this->storefrontIconsMappingProvider->getIconsMappingForAllThemes();
        $resolver->setDefaults(['choices' => array_flip($icons)]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return OroIconType::class;
    }
}
