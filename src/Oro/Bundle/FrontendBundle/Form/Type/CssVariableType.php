<?php

namespace Oro\Bundle\FrontendBundle\Form\Type;

use Oro\Bundle\FrontendBundle\Model\CssVariableConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Represents CSS settings for Theme Configuration
 */
class CssVariableType extends AbstractType
{
    public const string NAME = 'oro_frontend_css_variable_type';

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $parentConfig = $options['parentConfig'];

        $builder->add(
            'value',
            $parentConfig['class'],
            [
                'constraints' => $parentConfig['constraints'],
                ...$parentConfig['options']
            ]
        );
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => CssVariableConfig::class,
            ])
            ->setRequired(['parentConfig'])
            ->setDefined(['cssVariableName']);
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return self::NAME;
    }
}
