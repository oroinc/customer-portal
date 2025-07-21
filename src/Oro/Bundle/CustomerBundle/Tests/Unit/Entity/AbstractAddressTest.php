<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\CustomerBundle\Entity\AbstractDefaultTypedAddress;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;
use PHPUnit\Framework\TestCase;

abstract class AbstractAddressTest extends TestCase
{
    use EntityTestCaseTrait;

    abstract protected function createAddressEntity(): AbstractDefaultTypedAddress;

    public function testTypesCollection(): void
    {
        $address = $this->createAddressEntity();
        $billingType = new AddressType(AddressType::TYPE_BILLING);
        self::assertPropertyCollections($address, [['types', $billingType]]);
    }

    public function testProperties(): void
    {
        $address = $this->createAddressEntity();
        self::assertPropertyAccessors($address, [
            ['systemOrganization', new Organization()],
            ['owner', new User()],
            ['phone', '11111111111']
        ]);
    }

    public function testGetDefaults(): void
    {
        $address = $this->createAddressEntity();
        $billingType = new AddressType(AddressType::TYPE_BILLING);
        $shippingType = new AddressType(AddressType::TYPE_SHIPPING);

        $this->assertCount(0, $address->getDefaults());
        $this->assertInstanceOf(ArrayCollection::class, $address->getDefaults());
        $this->assertFalse($address->hasDefault('billing'));
        $this->assertFalse($address->hasDefault('shipping'));

        $address->addType($billingType);
        $address->addType($shippingType);
        $this->assertCount(0, $address->getDefaults());

        $address->setDefaults([$billingType, $shippingType]);
        $this->assertCount(2, $address->getDefaults());
        $this->assertTrue($address->hasDefault('billing'));
        $this->assertTrue($address->hasDefault('shipping'));
    }

    public function testSetDefaults(): void
    {
        $address = $this->createAddressEntity();
        $billingType = new AddressType(AddressType::TYPE_BILLING);
        $shippingType = new AddressType(AddressType::TYPE_SHIPPING);

        $types = new ArrayCollection([$billingType, $shippingType]);
        $this->assertCount(0, $address->getDefaults());
        $address->setTypes($types);
        $this->assertCount(0, $address->getDefaults());

        $this->assertSame($address, $address->setDefaults([$billingType]));

        $this->assertCount(1, $address->getDefaults());
        $address->setDefaults([$billingType, $shippingType]);
        $this->assertCount(2, $address->getDefaults());

        $address->setTypes(new ArrayCollection([$billingType]));
        $this->assertCount(0, $address->getDefaults());
        $address->setDefaults([$shippingType]);
        $this->assertCount(0, $address->getDefaults());
    }
}
