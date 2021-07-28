<?php

namespace Oro\Bundle\CustomerBundle\ImportExport\Serializer\Normalizer;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\ConfigurableEntityNormalizer;

class CustomerNormalizer extends ConfigurableEntityNormalizer
{
    /**
     * {@inheritdoc}
     * @param Customer $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
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

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof Customer;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, string $type, string $format = null, array $context = []): bool
    {
        return is_a($type, Customer::class, true);
    }
}
