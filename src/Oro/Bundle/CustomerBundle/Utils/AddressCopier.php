<?php

declare(strict_types=1);

namespace Oro\Bundle\CustomerBundle\Utils;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddressAwareInterface;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddressAwareInterface;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Copies one address into another.
 * Use it to create/update a specific address from an Address Book address.
 */
class AddressCopier
{
    private array $skipFields = ['id', 'created', 'updated'];

    public function __construct(private ManagerRegistry $doctrine, private PropertyAccessorInterface $propertyAccessor)
    {
    }

    public function setSkipFields(array $skipFields): void
    {
        $this->skipFields = $skipFields;
    }

    public function copyToAddress(
        AbstractAddress $fromAddress,
        AbstractAddress $toAddress
    ): void {
        $addressClassName = ClassUtils::getClass($fromAddress);
        $addressMetadata = $this->doctrine->getManagerForClass($addressClassName)->getClassMetadata($addressClassName);

        foreach ($addressMetadata->getFieldNames() as $fieldName) {
            if (in_array($fieldName, $this->skipFields, true)) {
                continue;
            }

            $this->setValue($fromAddress, $toAddress, $fieldName);
        }

        foreach ($addressMetadata->getAssociationNames() as $associationName) {
            if (in_array($associationName, $this->skipFields, true)) {
                continue;
            }

            $this->setValue($fromAddress, $toAddress, $associationName);
        }

        if ($toAddress instanceof CustomerAddressAwareInterface) {
            $toAddress->setCustomerAddress(null);

            if ($fromAddress instanceof CustomerAddress) {
                $toAddress->setCustomerAddress($fromAddress);
            }
        }

        if ($toAddress instanceof CustomerUserAddressAwareInterface) {
            $toAddress->setCustomerUserAddress(null);

            if ($fromAddress instanceof CustomerUserAddress) {
                $toAddress->setCustomerUserAddress($fromAddress);
            }
        }
    }

    private function setValue(AbstractAddress $fromAddress, AbstractAddress $toAddress, string $propertyName): void
    {
        try {
            $value = $this->propertyAccessor->getValue($fromAddress, $propertyName);

            $this->propertyAccessor->setValue($toAddress, $propertyName, $value);
        } catch (NoSuchPropertyException $exception) {
        }
    }
}
