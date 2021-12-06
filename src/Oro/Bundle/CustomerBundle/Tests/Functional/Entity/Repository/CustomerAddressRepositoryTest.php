<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Entity\Repository;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerAddressRepository;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerAddresses;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class CustomerAddressRepositoryTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient();
        $this->loadFixtures([LoadCustomerAddresses::class]);
    }

    private function getRepository(): CustomerAddressRepository
    {
        return self::getContainer()->get('doctrine')->getRepository(CustomerAddress::class);
    }

    /**
     * @dataProvider addressesDataProvider
     */
    public function testGetAddressesByType(string $customerReference, string $type, array $expectedAddressReferences)
    {
        /** @var Customer $customer */
        $customer = $this->getReference($customerReference);

        /** @var CustomerAddress[] $actual */
        $actual = $this->getRepository()->getAddressesByType(
            $customer,
            $type,
            $this->getContainer()->get('oro_security.acl_helper')
        );
        $this->assertCount(count($expectedAddressReferences), $actual);
        $addressIds = [];
        foreach ($actual as $address) {
            $addressIds[] = $address->getId();
        }
        foreach ($expectedAddressReferences as $addressReference) {
            $this->assertContains($this->getReference($addressReference)->getId(), $addressIds);
        }
    }

    public function addressesDataProvider(): array
    {
        return [
            [
                'customer.level_1',
                'billing',
                [
                    'customer.level_1.address_1',
                    'customer.level_1.address_2',
                    'customer.level_1.address_3'
                ]
            ],
            [
                'customer.level_1',
                'shipping',
                [
                    'customer.level_1.address_1',
                    'customer.level_1.address_3'
                ]
            ]
        ];
    }

    /**
     * @dataProvider defaultAddressDataProvider
     */
    public function testGetDefaultAddressesByType(
        string $customerReference,
        string $type,
        string $expectedAddressReference
    ) {
        /** @var Customer $customer */
        $customer = $this->getReference($customerReference);

        /** @var CustomerAddress[] $actual */
        $actual = $this->getRepository()->getDefaultAddressesByType(
            $customer,
            $type,
            $this->getContainer()->get('oro_security.acl_helper')
        );
        $this->assertCount(1, $actual);
        $this->assertEquals($this->getReference($expectedAddressReference)->getId(), $actual[0]->getId());
    }

    public function defaultAddressDataProvider(): array
    {
        return [
            [
                'customer.level_1',
                'billing',
                'customer.level_1.address_2'
            ],
            [
                'customer.level_1',
                'shipping',
                'customer.level_1.address_1'
            ]
        ];
    }
}
