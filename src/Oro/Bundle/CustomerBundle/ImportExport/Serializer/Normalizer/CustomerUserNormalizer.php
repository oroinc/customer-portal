<?php

namespace Oro\Bundle\CustomerBundle\ImportExport\Serializer\Normalizer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\ConfigurableEntityNormalizer;

/**
 * Adds extra normalizing logic of customer and owner when normalizing CustomerUser.
 */
class CustomerUserNormalizer extends ConfigurableEntityNormalizer
{
    /**
     * @param CustomerUser $object
     */
    #[\Override]
    public function normalize($object, ?string $format = null, array $context = [])
    {
        $result = parent::normalize($object, $format, $context);

        if (isset($result['customer']) && $object->getCustomer()) {
            $result['customer']['name'] = $object->getCustomer()->getName();
        }

        if (isset($result['owner']) && $object->getOwner()) {
            $result['owner']['id'] = $object->getOwner()->getId();
            unset($result['owner']['username']);
        }

        return $result;
    }

    #[\Override]
    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof CustomerUser;
    }

    #[\Override]
    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return is_a($type, CustomerUser::class, true) || is_a($type, CustomerUserRole::class, true);
    }

    #[\Override]
    protected function setObjectValue($object, $fieldName, $value)
    {
        if ($object instanceof CustomerUserRole && $fieldName === 'role') {
            $object->setRole($value, false);
        } else {
            parent::setObjectValue($object, $fieldName, $value);
        }
    }

    #[\Override]
    protected function getObjectValue($object, $fieldName)
    {
        $value = $this->fieldHelper->getObjectValue($object, $fieldName);

        if ($fieldName === 'userRoles' && !$value instanceof Collection) {
            $value = new ArrayCollection($value);
        }

        return $value;
    }
}
