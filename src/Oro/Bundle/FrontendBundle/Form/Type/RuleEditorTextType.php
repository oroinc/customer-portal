<?php

namespace Oro\Bundle\FrontendBundle\Form\Type;

use Oro\Bundle\FrontendBundle\Form\OptionsConfigurator\RuleEditorOptionsConfigurator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RuleEditorTextType extends AbstractType
{
    /**
     * @var RuleEditorOptionsConfigurator
     */
    private $optionsConfigurator;

    /**
     * @param RuleEditorOptionsConfigurator $optionsConfigurator
     */
    public function __construct(RuleEditorOptionsConfigurator $optionsConfigurator)
    {
        $this->optionsConfigurator = $optionsConfigurator;
    }

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
        return 'oro_frontend_rule_editor_text';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $this->optionsConfigurator->configureOptions($resolver);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return TextType::class;
    }
}
