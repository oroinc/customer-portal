<?php

namespace Oro\Bundle\CustomerBundle\ImportExport\Serializer\Normalizer;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

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
        
        if ($object->getOwner()) {
            $result['owner']['id'] = $object->getOwner()->getId();
            unset($result['owner']['username']);
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

    /**
     * {@inheritdoc}
     */
    protected function getFieldValueFromObject($object, $fieldName)
    {
        $value = $this->fieldHelper->getObjectValue($object, $fieldName);

        if ($fieldName === 'roles' && !$value instanceof Collection) {
            $value = new ArrayCollection($value);
        }

        return $value;
    }
}
