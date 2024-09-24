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
    #[\Override]
    public function getBlockPrefix(): string
    {
        return ImageType::NAME;
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'checkEmptyFile' => false,
                'allowDelete' => true,
            ]
        );
    }

    #[\Override]
    public function getParent(): ?string
    {
        return TextType::class;
    }
}
