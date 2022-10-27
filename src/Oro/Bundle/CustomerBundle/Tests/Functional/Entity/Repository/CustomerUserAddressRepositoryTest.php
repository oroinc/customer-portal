<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Entity\Repository;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerUserAddressRepository;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserAddresses;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class CustomerUserAddressRepositoryTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient();
        $this->loadFixtures([LoadCustomerUserAddresses::class]);
    }

    private function getRepository(): CustomerUserAddressRepository
    {
        return self::getContainer()->get('doctrine')->getRepository(CustomerUserAddress::class);
    }

    /**
     * @dataProvider addressesDataProvider
     */
    public function testGetAddressesByType(string $userReference, string $type, array $expectedAddressReferences)
    {
        /** @var CustomerUser $user */
        $user = $this->getReference($userReference);

        /** @var CustomerUserAddress[] $actual */
        $actual = $this->getRepository()->getAddressesByType(
            $user,
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
                'grzegorz.brzeczyszczykiewicz@example.com',
                'billing',
                [
                    'grzegorz.brzeczyszczykiewicz@example.com.address_1',
                    'grzegorz.brzeczyszczykiewicz@example.com.address_2',
                    'grzegorz.brzeczyszczykiewicz@example.com.address_3'
                ]
            ],
            [
                'grzegorz.brzeczyszczykiewicz@example.com',
                'shipping',
                [
                    'grzegorz.brzeczyszczykiewicz@example.com.address_1',
                    'grzegorz.brzeczyszczykiewicz@example.com.address_3'
                ]
            ]
        ];
    }

    /**
     * @dataProvider defaultAddressDataProvider
     */
    public function testGetDefaultAddressesByType(
        string $customerUserReference,
        string $type,
        string $expectedAddressReference
    ) {
        /** @var CustomerUser $user */
        $user = $this->getReference($customerUserReference);

        /** @var CustomerUserAddress[] $actual */
        $actual = $this->getRepository()->getDefaultAddressesByType(
            $user,
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
                'grzegorz.brzeczyszczykiewicz@example.com',
                'billing',
                'grzegorz.brzeczyszczykiewicz@example.com.address_2'
            ],
            [
                'grzegorz.brzeczyszczykiewicz@example.com',
                'shipping',
                'grzegorz.brzeczyszczykiewicz@example.com.address_1'
            ]
        ];
    }
}
