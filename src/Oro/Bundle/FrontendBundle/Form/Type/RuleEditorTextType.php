<?php

namespace Oro\Bundle\FrontendBundle\Form\Type;

use Oro\Bundle\FrontendBundle\Form\OptionsConfigurator\RuleEditorOptionsConfigurator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RuleEditorTextType extends AbstractType
{
    public const NAME = 'oro_frontend_rule_editor_text';

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
