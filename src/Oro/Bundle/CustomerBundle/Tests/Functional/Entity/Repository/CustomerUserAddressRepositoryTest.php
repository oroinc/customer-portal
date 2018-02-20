<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Entity\Repository;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerUserAddressRepository;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class CustomerUserAddressRepositoryTest extends WebTestCase
{
    /**
     * @var CustomerUserAddressRepository
     */
    protected $repository;

    protected function setUp()
    {
        $this->initClient();
        $this->client->useHashNavigation(true);
        $this->repository = $this->getContainer()
            ->get('doctrine')
            ->getRepository('OroCustomerBundle:CustomerUserAddress');

        $this->loadFixtures(
            [
                'Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserAddresses'
            ]
        );
    }

    /**
     * @dataProvider addressesDataProvider
     * @param string $userReference
     * @param string $type
     * @param array $expectedAddressReferences
     */
    public function testGetAddressesByType($userReference, $type, array $expectedAddressReferences)
    {
        /** @var CustomerUser $user */
        $user = $this->getReference($userReference);

        /** @var CustomerUserAddress[] $actual */
        $actual = $this->repository->getAddressesByType(
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

    /**
     * @return array
     */
    public function addressesDataProvider()
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
     * @param string $customerUserReference
     * @param string $type
     * @param string $expectedAddressReference
     */
    public function testGetDefaultAddressesByType($customerUserReference, $type, $expectedAddressReference)
    {
        /** @var CustomerUser $user */
        $user = $this->getReference($customerUserReference);

        /** @var CustomerUserAddress[] $actual */
        $actual = $this->repository->getDefaultAddressesByType(
            $user,
            $type,
            $this->getContainer()->get('oro_security.acl_helper')
        );
        $this->assertCount(1, $actual);
        $this->assertEquals($this->getReference($expectedAddressReference)->getId(), $actual[0]->getId());
    }

    /**
     * @return array
     */
    public function defaultAddressDataProvider()
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
