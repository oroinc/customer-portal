<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api\RestJsonApi;

use Oro\Bundle\AddressBundle\Tests\Functional\DataFixtures\LoadCountriesAndRegions;
use Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApiUpdateListTestCase;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;

/**
 * @dbIsolationPerTest
 */
class CustomerUserAddressUpdateListTest extends RestJsonApiUpdateListTestCase
{
    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures([
            LoadCountriesAndRegions::class,
            '@OroCustomerBundle/Tests/Functional/Api/DataFixtures/customer_users_for_address_update_list.yml'
        ]);
    }

    private function getCustomerUserAddressId(string $street): int
    {
        /** @var CustomerUserAddress|null $address */
        $address = $this->getEntityManager()->getRepository(CustomerUserAddress::class)
            ->findOneBy(['street' => $street]);
        if (null === $address) {
            throw new \RuntimeException(sprintf('The address "%s" not found.', $street));
        }

        return $address->getId();
    }

    private function getCustomerUserAddressIndex(array $data, string $street): int
    {
        foreach ($data as $i => $item) {
            if ($item['attributes']['street'] === $street) {
                return $i;
            }
        }
        throw new \RuntimeException(sprintf('The address "%s" not found.', $street));
    }

    public function testCreateEntities(): void
    {
        $data = [
            'data' => [
                [
                    'type'          => 'customeruseraddresses',
                    'attributes'    => [
                        'street'       => '15a Lewis Circle',
                        'city'         => 'Wilmington',
                        'postalCode'   => '90002',
                        'types'        => [
                            ['default' => true, 'addressType' => 'billing']
                        ],
                        'organization' => 'SOI-73 STR5 Account 2',
                        'validatedAt'  => null,
                    ],
                    'relationships' => [
                        'country'      => [
                            'data' => ['type' => 'countries', 'id' => 'US']
                        ],
                        'region'       => [
                            'data' => ['type' => 'regions', 'id' => 'US-AL']
                        ],
                        'customerUser' => [
                            'data' => ['type' => 'customerusers', 'id' => '<toString(@customer_user1->id)>']
                        ]
                    ]
                ],
                [
                    'type'          => 'customeruseraddresses',
                    'attributes'    => [
                        'street'       => '16 Lewis Circle',
                        'city'         => 'Wilmington',
                        'postalCode'   => '90002',
                        'types'        => [
                            ['default' => true, 'addressType' => 'shipping']
                        ],
                        'organization' => 'SOI-73 STR5 Account 2',
                        'validatedAt'  => '2024-10-11T00:00:00Z',
                    ],
                    'relationships' => [
                        'country'      => [
                            'data' => ['type' => 'countries', 'id' => 'US']
                        ],
                        'region'       => [
                            'data' => ['type' => 'regions', 'id' => 'US-AL']
                        ],
                        'customerUser' => [
                            'data' => ['type' => 'customerusers', 'id' => '<toString(@customer_user1->id)>']
                        ]
                    ]
                ]
            ]
        ];
        $this->processUpdateList(CustomerUserAddress::class, $data);

        $response = $this->get(
            ['entity' => 'customerusers', 'id' => '<toString(@customer_user1->id)>'],
            ['include' => 'addresses']
        );
        $address1Id = $this->getCustomerUserAddressId('15a Lewis Circle');
        $address2Id = $this->getCustomerUserAddressId('16 Lewis Circle');
        $responseContent = [
            'data'     => [
                'type'          => 'customerusers',
                'id'            => '<toString(@customer_user1->id)>',
                'relationships' => [
                    'addresses' => [
                        'data' => [
                            ['type' => 'customeruseraddresses', 'id' => (string)$address1Id],
                            ['type' => 'customeruseraddresses', 'id' => (string)$address2Id]
                        ]
                    ]
                ]
            ],
            'included' => $data['data']
        ];
        $address1Index = $this->getCustomerUserAddressIndex($responseContent['included'], '15a Lewis Circle');
        $address2Index = $this->getCustomerUserAddressIndex($responseContent['included'], '16 Lewis Circle');
        $responseContent['included'][$address1Index]['id'] = (string)$address1Id;
        $responseContent['included'][$address1Index]['attributes']['primary'] = true;
        $responseContent['included'][$address1Index]['attributes']['validatedAt'] = null;
        $responseContent['included'][$address2Index]['id'] = (string)$address2Id;
        $responseContent['included'][$address2Index]['attributes']['primary'] = false;
        $responseContent['included'][$address2Index]['attributes']['validatedAt'] = '2024-10-11T00:00:00Z';
        $this->assertResponseContains($responseContent, $response);
    }
}
