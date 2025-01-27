<?php

declare(strict_types=1);

namespace Oro\Bundle\AddressValidationBundle\ResolvedAddress;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\AddressValidationBundle\Model\ResolvedAddress;
use Oro\Bundle\CustomerBundle\Entity\AddressBookAwareInterface;
use Oro\Bundle\CustomerBundle\Utils\AddressBookAddressUtils;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Accepts the resolved address by copying it to the cloned original address.
 */
class ResolvedAddressAcceptor implements ResolvedAddressAcceptorInterface
{
    /**
     * @param PropertyAccessorInterface $propertyAccessor
     * @param array<string> $addressValidationFields
     */
    public function __construct(
        private PropertyAccessorInterface $propertyAccessor,
        private array $addressValidationFields
    ) {
    }

    public function acceptResolvedAddress(ResolvedAddress $resolvedAddress): AbstractAddress
    {
        $acceptedAddress = clone $resolvedAddress->getOriginalAddress();

        if ($acceptedAddress instanceof AddressBookAwareInterface) {
            AddressBookAddressUtils::resetAddressBookAddress($acceptedAddress);
        }

        foreach ($this->addressValidationFields as $fieldName) {
            $fieldValue = $this->propertyAccessor->getValue($resolvedAddress, $fieldName);

            $this->propertyAccessor->setValue($acceptedAddress, $fieldName, $fieldValue);
        }

        return $acceptedAddress;
    }
}
