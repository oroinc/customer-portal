<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\TestEntity;

use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\CustomerBundle\Entity\AbstractDefaultTypedAddress;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddressToAddressType;

/**
 * CustomerAddressToAddressType for testing purposes
 */
class TestCustomerAddressToAddressType extends CustomerAddressToAddressType
{
    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public static function create(int $id, string $typeName, AbstractDefaultTypedAddress $address): self
    {
        return (new self)->setId($id)->setType(new AddressType($typeName))->setAddress($address);
    }
}
