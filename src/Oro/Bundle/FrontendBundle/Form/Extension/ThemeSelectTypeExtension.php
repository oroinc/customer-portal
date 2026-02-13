<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Form\Extension;

use Oro\Bundle\ThemeBundle\Form\Type\ThemeSelectType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Sets default theme_group to 'commerce' for frontend applications.
 */
class ThemeSelectTypeExtension extends AbstractTypeExtension
{
    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('theme_group', 'commerce');
    }

    #[\Override]
    public static function getExtendedTypes(): iterable
    {
        return [ThemeSelectType::class];
    }
}
