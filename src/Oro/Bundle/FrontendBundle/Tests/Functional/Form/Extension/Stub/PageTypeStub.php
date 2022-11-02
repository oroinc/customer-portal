<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional\Form\Extension\Stub;

use Oro\Bundle\CMSBundle\Form\Type\WYSIWYGType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class PageTypeStub extends AbstractType
{
    public const NAME = 'oro_frontend_page_stub';

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'content',
            WYSIWYGType::class,
            [
                'label' => 'label',
                'required' => false
            ]
        );
    }
}
