<?php

namespace Oro\Bundle\CustomerBundle\Api;

use Oro\Bundle\CustomerBundle\Entity\AbstractDefaultTypedAddress;
use Oro\Component\EntitySerializer\DataAccessorInterface;

/**
 * The entity data accessor decorator that substitutes "types" property of AbstractDefaultTypedAddress
 * with "addressTypes" property.
 * It is required because this entity has "types" property, but its "getTypes()" method returns
 * completely different data.
 * @see \Oro\Bundle\CustomerBundle\Entity\AbstractDefaultTypedAddress::getTypes
 */
class AddressEntityDataAccessor implements DataAccessorInterface
{
    private DataAccessorInterface $innerDataAccessor;

    public function __construct(DataAccessorInterface $innerDataAccessor)
    {
        $this->innerDataAccessor = $innerDataAccessor;
    }

    /**
     * {@inheritDoc}
     */
    public function hasGetter(string $className, string $property): bool
    {
        if ('types' === $property && is_a($className, AbstractDefaultTypedAddress::class, true)) {
            $property = 'addressTypes';
        }

        return $this->innerDataAccessor->hasGetter($className, $property);
    }

    /**
     * {@inheritDoc}
     */
    public function tryGetValue(object|array $object, string $property, mixed &$value): bool
    {
        if ('types' === $property && $object instanceof AbstractDefaultTypedAddress) {
            $property = 'addressTypes';
        }

        return $this->innerDataAccessor->tryGetValue($object, $property, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function getValue(object|array $object, string $property): mixed
    {
        if ('types' === $property && $object instanceof AbstractDefaultTypedAddress) {
            $property = 'addressTypes';
        }

        return $this->innerDataAccessor->getValue($object, $property);
    }
}
