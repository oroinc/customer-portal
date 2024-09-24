<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Extension\Stub;

use Oro\Bundle\FormBundle\Form\Type\OroRichTextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OroRichTextTypeStub extends OroRichTextType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    }

    public function __construct()
    {
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver)
    {
        $defaultWysiwygOptions = [
            'statusbar' => '',
            'resize' => '',
            'width' => '',
            'height' => '',
            'plugins' => '',
            'toolbar' => '',
        ];

        $defaults = [
            'wysiwyg_options' => $defaultWysiwygOptions,
        ];

        $resolver->setDefaults($defaults);
    }
}
