<?php

namespace Oro\Bundle\CustomerBundle\ImportExport\Serializer\Normalizer;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\ConfigurableEntityNormalizer;

class CustomerUserNormalizer extends ConfigurableEntityNormalizer
{
    /**
     * {@inheritdoc}
     * @param CustomerUser $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $result = parent::normalize($object, $format, $context);

        if ($object->getCustomer()) {
            $result['customer']['name'] = $object->getCustomer()->getName();
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return $data instanceof CustomerUser;
    }
}
