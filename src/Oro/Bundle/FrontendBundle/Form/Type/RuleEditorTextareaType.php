<?php

namespace Oro\Bundle\FrontendBundle\Form\Type;

use Oro\Bundle\FrontendBundle\Form\OptionsConfigurator\RuleEditorOptionsConfigurator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Provides a textarea form type for editing rules with syntax highlighting and validation.
 *
 * This form type extends the standard textarea to provide rule editor functionality with
 * configurable options for syntax highlighting, validation, and editor behavior. It uses
 * the RuleEditorOptionsConfigurator to apply consistent rule editor settings across the form.
 */
class RuleEditorTextareaType extends AbstractType
{
    const NAME = 'oro_frontend_rule_editor';

    /**
     * @var RuleEditorOptionsConfigurator
     */
    private $optionsConfigurator;

    public function __construct(RuleEditorOptionsConfigurator $optionsConfigurator)
    {
        $this->optionsConfigurator = $optionsConfigurator;
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver)
    {
        $this->optionsConfigurator->configureOptions($resolver);
    }

    public function getName()
    {
        return $this->getBlockPrefix();
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return self::NAME;
    }

    #[\Override]
    public function getParent(): ?string
    {
        return TextareaType::class;
    }
}
