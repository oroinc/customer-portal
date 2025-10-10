<?php

namespace Oro\Bundle\CustomerBundle\ImportExport\Serializer\Normalizer;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\ConfigurableEntityNormalizer;

/**
 * Customizes Customer entity serialization for import/export by filtering out sensitive relationship data
 */
class CustomerNormalizer extends ConfigurableEntityNormalizer
{
    /**
     * @param Customer $object
     */
    #[\Override]
    public function normalize(
        mixed $object,
        ?string $format = null,
        array $context = []
    ): float|int|bool|\ArrayObject|array|string|null {
        $result = parent::normalize($object, $format, $context);

        if (isset($result['owner']) && $object->getOwner()) {
            $result['owner']['id'] = $object->getOwner()->getId();
            unset($result['owner']['username']);
        }

        if (isset($result['parent']) && $object->getParent()) {
            $result['parent']['id'] = $object->getParent()->getId();
            unset($result['parent']['name']);
        }

        return $result;
    }

    #[\Override]
    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Customer;
    }

    #[\Override]
    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return is_a($type, Customer::class, true);
    }
}
