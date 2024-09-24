<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type\Stub;

use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\CustomerBundle\Form\DataTransformer\AddressTypeDefaultTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerTypedAddressWithDefaultTypeStub extends AbstractType
{
    private const NAME = 'oro_customer_typed_address_with_default';

    private array $types;
    private EntityManagerInterface $em;

    public function __construct(array $types, EntityManagerInterface $em)
    {
        $this->types = $types;
        $this->em = $em;
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = [];
        /** @var AddressType $type */
        foreach ($this->types as $type) {
            $typeName = $type->getName();
            $choices['Default' . $typeName] = $typeName;
        }

        $builder->add('default', ChoiceType::class, [
            'choices'  => $choices,
            'multiple' => true,
            'expanded' => true,
            'label'    => false,
        ])
        ->addViewTransformer(new AddressTypeDefaultTransformer($this->em));
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'em'       => null,
            'property' => null
        ]);

        $resolver->setRequired([
            'class'
        ]);
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return self::NAME;
    }
}
