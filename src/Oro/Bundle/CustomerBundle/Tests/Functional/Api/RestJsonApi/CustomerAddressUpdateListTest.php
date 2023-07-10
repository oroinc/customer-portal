<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api\RestJsonApi;

use Oro\Bundle\AddressBundle\Tests\Functional\DataFixtures\LoadCountriesAndRegions;
use Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApiUpdateListTestCase;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;

/**
 * @dbIsolationPerTest
 */
class CustomerAddressUpdateListTest extends RestJsonApiUpdateListTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures([
            LoadCountriesAndRegions::class,
            '@OroCustomerBundle/Tests/Functional/Api/DataFixtures/customers_for_address_update_list.yml'
        ]);
    }

    public function testCreateEntities(): void
    {
        $data = [
            'data' => [
                [
                    'type'          => 'customeraddresses',
                    'attributes'    => [
                        'street'       => '15a Lewis Circle',
                        'city'         => 'Wilmington',
                        'postalCode'   => '90002',
                        'types'        => [
                            ['default' => true, 'addressType' => 'billing']
                        ],
                        'organization' => 'SOI-73 STR5 Account 2'
                    ],
                    'relationships' => [
                        'country'  => [
                            'data' => ['type' => 'countries', 'id' => 'US']
                        ],
                        'region'   => [
                            'data' => ['type' => 'regions', 'id' => 'US-AL']
                        ],
                        'customer' => [
                            'data' => ['type' => 'customers', 'id' => '<toString(@customer1->id)>']
                        ]
                    ]
                ],
                [
                    'type'          => 'customeraddresses',
                    'attributes'    => [
                        'street'       => '16 Lewis Circle',
                        'city'         => 'Wilmington',
                        'postalCode'   => '90002',
                        'types'        => [
                            ['default' => true, 'addressType' => 'shipping']
                        ],
                        'organization' => 'SOI-73 STR5 Account 2'
                    ],
                    'relationships' => [
                        'country'  => [
                            'data' => ['type' => 'countries', 'id' => 'US']
                        ],
                        'region'   => [
                            'data' => ['type' => 'regions', 'id' => 'US-AL']
                        ],
                        'customer' => [
                            'data' => ['type' => 'customers', 'id' => '<toString(@customer1->id)>']
                        ]
                    ]
                ]
            ]
        ];
        $this->processUpdateList(CustomerAddress::class, $data);

        $response = $this->get(
            ['entity' => 'customers', 'id' => '<toString(@customer1->id)>'],
            ['include' => 'addresses']
        );
        $responseContent = [
            'data'     => [
                'type'          => 'customers',
                'id'            => '<toString(@customer1->id)>',
                'relationships' => [
                    'addresses' => [
                        'data' => [
                            ['type' => 'customeraddresses', 'id' => 'new'],
                            ['type' => 'customeraddresses', 'id' => 'new']
                        ]
                    ]
                ]
            ],
            'included' => $data['data']
        ];
        $responseContent['included'][0]['id'] = 'new';
        $responseContent['included'][0]['attributes']['primary'] = true;
        $responseContent['included'][1]['id'] = 'new';
        $responseContent['included'][1]['attributes']['primary'] = false;
        $responseContent = $this->updateResponseContent($responseContent, $response);
        $this->assertResponseContains($responseContent, $response);
    }
}
