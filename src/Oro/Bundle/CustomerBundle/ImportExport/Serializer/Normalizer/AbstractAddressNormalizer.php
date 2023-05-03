<?php

namespace Oro\Bundle\CustomerBundle\ImportExport\Serializer\Normalizer;

use Oro\Bundle\AddressBundle\Entity\AbstractTypedAddress;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\ConfigurableEntityNormalizer;

/**
 * Abstract implementation of typed address normalizer.
 * Provides support of human friendly types.
 */
abstract class AbstractAddressNormalizer extends ConfigurableEntityNormalizer
{
    /**
     * @param AbstractTypedAddress $object
     */
    public function normalize($object, string $format = null, array $context = []): array
    {
        $result = parent::normalize($object, $format, $context);

        if (!$this->supportsExtraSerializableColumns($context)) {
            return $result;
        }

        $result['Billing'] = $this->serializer->normalize(
            $object->hasTypeWithName(AddressType::TYPE_BILLING),
            $format,
            $context
        );
        $result['Default Billing'] = $this->serializer->normalize(
            $object->hasDefault(AddressType::TYPE_BILLING),
            $format,
            $context
        );

        $result['Shipping'] = $this->serializer->normalize(
            $object->hasTypeWithName(AddressType::TYPE_SHIPPING),
            $format,
            $context
        );
        $result['Default Shipping'] = $this->serializer->normalize(
            $object->hasDefault(AddressType::TYPE_SHIPPING),
            $format,
            $context
        );

        return $result;
    }

    public function denormalize($data, string $type, string $format = null, array $context = []): AbstractTypedAddress
    {
        foreach (['Shipping', 'Default Shipping', 'Billing', 'Default Billing'] as $field) {
            $data[$field] = $this->scalarFieldDenormalizer->denormalize(
                $data[$field] ?? null,
                'boolean',
                $format,
                $context
            );
        }
        if ($data['Shipping'] || $data['Default Shipping']) {
            $data['types'][] = [
                'type' => [
                    'name' => AddressType::TYPE_SHIPPING
                ],
                'default' => $data['Default Shipping']
            ];
        }
        if ($data['Billing'] || $data['Default Billing']) {
            $data['types'][] = [
                'type' => [
                    'name' => AddressType::TYPE_BILLING
                ],
                'default' => $data['Default Billing']
            ];
        }

        return parent::denormalize($data, $type, $format, $context);
    }

    protected function supportsExtraSerializableColumns(array $context): bool
    {
        return isset($context['entityName']) && is_a(
            $context['entityName'],
            AbstractTypedAddress::class,
            true
        );
    }
}
