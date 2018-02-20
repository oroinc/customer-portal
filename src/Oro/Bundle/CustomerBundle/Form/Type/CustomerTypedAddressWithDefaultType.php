<?php

namespace Oro\Bundle\CustomerBundle\Form\Type;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\CustomerBundle\Form\DataTransformer\AddressTypeDefaultTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

class CustomerTypedAddressWithDefaultType extends AbstractType
{
    const NAME = 'oro_customer_typed_address_with_default';

    /** @var ManagerRegistry */
    protected $registry;

    /** @var TranslatorInterface */
    protected $translator;

    /**
     * {@inheritdoc}
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['em'] === null) {
            $em = $this->registry->getManagerForClass($options['class']);
        } else {
            $em = $this->registry->getManager($options['em']);
        }

        $repository = $em->getRepository($options['class']);
        $entitiesIterator = $repository->getBatchIterator();

        /** @var ClassMetadataInfo $classMetadata */
        $classMetadata   = $em->getClassMetadata($options['class']);
        $identifierField = $classMetadata->getSingleIdentifierFieldName();

        $choiceLabels = [];

        /** @var AddressType $entity */
        foreach ($entitiesIterator as $entity) {
            $pkValue = $classMetadata->getReflectionProperty($identifierField)->getValue($entity);

            if ($options['property']) {
                $value = $classMetadata->getReflectionProperty($options['property'])->getValue($entity);
            } else {
                $value = (string)$entity;
            }

            $choiceLabels[$pkValue] = $this->translator->trans(
                'oro.customer.customer_typed_address_with_default_type.choice.default_text',
                [
                    '%type_name%' => $value
                ]
            );
        }

        $builder->add('default', 'choice', [
            'choices'  => $choiceLabels,
            'multiple' => true,
            'expanded' => true,
            'label'    => false,
        ])
        ->addViewTransformer(new AddressTypeDefaultTransformer($em));
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * @param ManagerRegistry $registry
     */
    public function setRegistry(ManagerRegistry $registry)
    {
        $this->registry = $registry;
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
        return self::NAME;
    }
}
