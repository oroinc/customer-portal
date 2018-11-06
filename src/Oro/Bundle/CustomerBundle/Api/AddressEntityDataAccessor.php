<?php

namespace Oro\Bundle\CustomerBundle\Api;

use Oro\Bundle\CustomerBundle\Entity\AbstractDefaultTypedAddress;
use Oro\Component\EntitySerializer\DataAccessorInterface;

/**
 * The entity data accessor decorator that substitutes "types" property of AbstractDefaultTypedAddress
 * with "addressTypes" property.
 * This is required because this entity has "types" property, but its "getTypes()" method returns
 * completely different data.
 * @see \Oro\Bundle\CustomerBundle\Entity\AbstractDefaultTypedAddress::getTypes
 * This class can be removed if EntitySerializer component will be reimplemented
 * to use DBAL instead of ORM (BAP-10066).
 */
class AddressEntityDataAccessor implements DataAccessorInterface
{
    /** @var DataAccessorInterface */
    private $innerDataAccessor;

    /**
     * @param DataAccessorInterface $innerDataAccessor
     */
    public function __construct(DataAccessorInterface $innerDataAccessor)
    {
        $this->innerDataAccessor = $innerDataAccessor;
    }

    /**
     * {@inheritdoc}
     */
    public function hasGetter($className, $property)
    {
        if ('types' === $property && \is_a($className, AbstractDefaultTypedAddress::class, true)) {
            $property = 'addressTypes';
        }

        return $this->innerDataAccessor->hasGetter($className, $property);
    }

    /**
     * {@inheritdoc}
     */
    public function tryGetValue($object, $property, &$value)
    {
        if ('types' === $property && $object instanceof AbstractDefaultTypedAddress) {
            $property = 'addressTypes';
        }

        return $this->innerDataAccessor->tryGetValue($object, $property, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getValue($object, $property)
    {
        if ('types' === $property && $object instanceof AbstractDefaultTypedAddress) {
            $property = 'addressTypes';
        }

        return $this->innerDataAccessor->getValue($object, $property);
    }
}
