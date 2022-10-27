<?php
namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type\Stub;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FrontendOwnerSelectTypeStub extends AbstractType
{
    private const NAME = 'oro_customer_frontend_owner_select';

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
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'choice_label' => null,
                'class' => null,
                'targetObject' => null,
                'query_builder' => null,
            ]
        );
    }
}
