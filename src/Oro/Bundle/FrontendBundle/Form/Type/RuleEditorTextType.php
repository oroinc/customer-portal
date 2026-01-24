<?php

namespace Oro\Bundle\FrontendBundle\Form\Type;

use Oro\Bundle\FrontendBundle\Form\OptionsConfigurator\RuleEditorOptionsConfigurator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Provides a text input form type for editing rules with syntax highlighting and validation.
 *
 * This form type extends the standard text input to provide rule editor functionality with
 * configurable options for syntax highlighting, validation, and editor behavior. It uses
 * the RuleEditorOptionsConfigurator to apply consistent rule editor settings across the form.
 */
class RuleEditorTextType extends AbstractType
{
    const NAME = 'oro_frontend_rule_editor_text';

    /**
     * @var RuleEditorOptionsConfigurator
     */
    private $optionsConfigurator;

    public function __construct(RuleEditorOptionsConfigurator $optionsConfigurator)
    {
        $this->optionsConfigurator = $optionsConfigurator;
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
    public function configureOptions(OptionsResolver $resolver)
    {
        $this->optionsConfigurator->configureOptions($resolver);
    }

    #[\Override]
    public function getParent(): ?string
    {
        return TextType::class;
    }
}
