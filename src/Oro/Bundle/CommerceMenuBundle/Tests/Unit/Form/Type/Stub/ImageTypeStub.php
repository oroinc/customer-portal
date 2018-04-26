<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Form\Type\Stub;

use Oro\Bundle\AttachmentBundle\Form\Type\ImageType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImageTypeStub extends AbstractType
{
    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return ImageType::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'checkEmptyFile' => false,
                'allowDelete' => true,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return TextType::class;
    }
}
