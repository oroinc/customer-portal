<?php

namespace Oro\Bundle\CustomerBundle\ImportExport\Serializer\Normalizer;

use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;

/**
 * Entity normalizer for Customer User Address.
 * Adds CustomerUser.id (frontendOwner:id) to the result.
 */
class CustomerUserAddressNormalizer extends AbstractAddressNormalizer
{
    /**
     * @param CustomerUserAddress $object
     */
    public function normalize($object, string $format = null, array $context = []): array
    {
        $result = parent::normalize($object, $format, $context);

        if (!$this->supportsExtraSerializableColumns($context)) {
            return $result;
        }

        $result['frontendOwner']['id'] = $object->getFrontendOwner()->getId();

        return $result;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof CustomerUserAddress;
    }

    public function supportsDenormalization($data, string $type, string $format = null, array $context = []): bool
    {
        return is_a($type, CustomerUserAddress::class, true);
    }
}
