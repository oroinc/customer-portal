<?php

declare(strict_types=1);

namespace Oro\Bundle\AddressValidationBundle\Form\Type;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\AddressValidationBundle\Model\ResolvedAddress;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Represents a list of suggested addresses to display for a user.
 */
class AddressValidationResultType extends AbstractType
{
    public function __construct(
        private DataTransformerInterface $resolvedAddressAcceptingTransformer
    ) {
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            $builder
                ->create(
                    'address',
                    ChoiceType::class,
                    [
                        'expanded' => true,
                        'multiple' => false,
                        'choices' => [$options['original_address'], ...$options['suggested_addresses']],
                    ]
                )
                ->addModelTransformer($this->resolvedAddressAcceptingTransformer)
        );
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'validation_groups' => false,
        ]);

        $resolver
            ->define('suggested_addresses')
            ->required()
            ->allowedTypes(ResolvedAddress::class . '[]');

        $resolver
            ->define('original_address')
            ->required()
            ->allowedTypes(AbstractAddress::class);
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'oro_address_validation_result';
    }
}
