<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Traits;

use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\CustomerBundle\Entity\AbstractDefaultTypedAddress;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;

trait AddressEntityTestTrait
{
    use EntityTestCaseTrait;

    public function testAddressesCollection()
    {
        $customer = $this->createTestedEntity();
        self::assertPropertyCollections($customer, [['addresses', $this->createAddressEntity()]]);
    }

    /**
     * @param AbstractDefaultTypedAddress[]    $addresses
     * @param string                           $searchName
     * @param AbstractDefaultTypedAddress|null $expectedAddress
     * @dataProvider getAddressByTypeNameProvider
     */
    public function testGetAddressByTypeName(
        array $addresses,
        string $searchName,
        ?AbstractDefaultTypedAddress $expectedAddress
    ) {
        $customer = $this->createTestedEntity();
        foreach ($addresses as $address) {
            $customer->addAddress($address);
        }

        $actualAddress = $customer->getAddressByTypeName($searchName);
        \PHPUnit\Framework\Assert::assertEquals($expectedAddress, $actualAddress);
    }

    public function getAddressByTypeNameProvider(): array
    {
        $billingType = new AddressType(AddressType::TYPE_BILLING);
        $shippingType = new AddressType(AddressType::TYPE_SHIPPING);

        $addressWithBilling = $this->createAddressEntity();
        $addressWithBilling->addType($billingType);

        $addressWithShipping = $this->createAddressEntity();
        $addressWithShipping->addType($shippingType);

        $addressWithShippingAndBilling = $this->createAddressEntity();
        $addressWithShippingAndBilling->addType($shippingType);
        $addressWithShippingAndBilling->addType($billingType);

        return [
            'not found address with type (empty addresses)' => [
                'addresses' => [],
                'searchName' => AddressType::TYPE_BILLING,
                'expectedAddress' => null
            ],
            'not found address with type (some address exists)' => [
                'addresses' => [$addressWithShipping],
                'searchName' => AddressType::TYPE_BILLING,
                'expectedAddress' => null
            ],
            'find address by shipping name' => [
                'addresses' => [$addressWithShipping],
                'searchName' => AddressType::TYPE_SHIPPING,
                'expectedAddress' => $addressWithShipping
            ],
            'find first address by shipping name' => [
                'addresses' => [$addressWithShippingAndBilling, $addressWithShipping],
                'searchName' => AddressType::TYPE_SHIPPING,
                'expectedAddress' => $addressWithShippingAndBilling
            ],
        ];
    }

    /**
     * @dataProvider getPrimaryAddressProvider
     */
    public function testGetPrimaryAddress(array $addresses, ?AbstractDefaultTypedAddress $expectedAddress)
    {
        $customer = $this->createTestedEntity();
        foreach ($addresses as $address) {
            $customer->addAddress($address);
        }

        \PHPUnit\Framework\Assert::assertEquals($expectedAddress, $customer->getPrimaryAddress());
    }

    public function getPrimaryAddressProvider(): array
    {
        $primaryAddress = $this->createAddressEntity();
        $primaryAddress->setPrimary(true);

        $notPrimaryAddress = $this->createAddressEntity();

        return [
            'without primary address' => [
                'addresses' => [$notPrimaryAddress],
                'expectedAddress' => null
            ],
            'one primary address' => [
                'addresses' => [$primaryAddress],
                'expectedAddress' => $primaryAddress
            ],
            'get one primary by few address' => [
                'addresses' => [$primaryAddress, $notPrimaryAddress],
                'expectedAddress' => $primaryAddress
            ],
        ];
    }

    /**
     * Return tested entity
     *
     * @return CustomerUser|Customer
     */
    abstract protected function createTestedEntity();

    /**
     * Return address entity related with entity
     * returned from `createTestedEntity`
     *
     * @return AbstractDefaultTypedAddress
     */
    abstract protected function createAddressEntity();
}
