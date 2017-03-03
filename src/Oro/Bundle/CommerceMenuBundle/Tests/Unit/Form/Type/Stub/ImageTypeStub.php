<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Form\Type\Stub;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Oro\Bundle\AttachmentBundle\Form\Type\ImageType;

class ImageTypeStub extends AbstractType
{
    /**
     * @return string
     */
    public function getName()
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
