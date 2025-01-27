<?php

namespace Oro\Bundle\AddressValidationBundle\Form\Type;

use Oro\Bundle\AddressValidationBundle\Provider\AddressValidationSupportedChannelTypesProvider;
use Oro\Bundle\IntegrationBundle\Form\Type\ConfigIntegrationSelectType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for selecting the integration channel supporting the address validation feature.
 */
class AddressValidationAwareConfigIntegrationSelectType extends AbstractType
{
    public function __construct(
        private AddressValidationSupportedChannelTypesProvider $addressValidationSupportedChannelTypesProvider
    ) {
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $channelTypes = $this->addressValidationSupportedChannelTypesProvider->getChannelTypes();

        $resolver->setDefaults([
            'configs' => [
                'placeholder' =>
                    'oro.address_validation.system_configuration.fields.address_validation_service.disabled',
            ],
            'allowed_types' => $channelTypes,
        ]);

        $resolver->setNormalizer(
            'allowed_types',
            static fn (Options $options, $value) => array_unique(array_merge($value ?? [], $channelTypes))
        );
    }

    #[\Override]
    public function getParent(): string
    {
        return ConfigIntegrationSelectType::class;
    }
}
