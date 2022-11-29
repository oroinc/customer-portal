<?php

namespace Oro\Bundle\CustomerBundle\ImportExport\Serializer\Normalizer;

use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;

/**
 * Entity normalizer for Customer User Address.
 */
class CustomerAddressNormalizer extends AbstractAddressNormalizer
{
    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof CustomerAddress;
    }

    public function supportsDenormalization($data, string $type, string $format = null, array $context = []): bool
    {
        return is_a($type, CustomerAddress::class, true);
    }
}
