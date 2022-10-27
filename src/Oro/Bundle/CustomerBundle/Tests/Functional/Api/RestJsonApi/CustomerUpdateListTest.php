<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api\RestJsonApi;

use Oro\Bundle\AddressBundle\Tests\Functional\DataFixtures\LoadCountriesAndRegions;
use Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApiUpdateListTestCase;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;

/**
 * @dbIsolationPerTest
 */
class CustomerUpdateListTest extends RestJsonApiUpdateListTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures(
            [
                LoadCountriesAndRegions::class,
                '@OroCustomerBundle/Tests/Functional/Api/DataFixtures/load_customers.yml',
            ]
        );
    }

    public function testCreateEntities()
    {
        $this->processUpdateList(
            Customer::class,
            [
                'data' => [
                    [
                        'type'       => 'customers',
                        'attributes' => ['name' => 'New Customer 1']
                    ],
                    [
                        'type'       => 'customers',
                        'attributes' => ['name' => 'New Customer 2']
                    ]
                ]
            ]
        );

        $response = $this->cget(
            ['entity' => 'customers'],
            ['fields[customers]' => 'name', 'filter[id][gte]' => '@customer1->id']
        );
        $responseContent = $this->updateResponseContent(
            [
                'data' => [
                    [
                        'type'       => 'customers',
                        'id'         => '<toString(@customer1->id)>',
                        'attributes' => ['name' => 'Customer 1']
                    ],
                    [
                        'type'       => 'customers',
                        'id'         => '<toString(@customer2->id)>',
                        'attributes' => ['name' => 'Customer 2']
                    ],
                    [
                        'type'       => 'customers',
                        'id'         => 'new',
                        'attributes' => ['name' => 'New Customer 1']
                    ],
                    [
                        'type'       => 'customers',
                        'id'         => 'new',
                        'attributes' => ['name' => 'New Customer 2']
                    ]
                ]
            ],
            $response
        );
        $this->assertResponseContains($responseContent, $response);
    }

    public function testUpdateEntities()
    {
        $this->processUpdateList(
            Customer::class,
            [
                'data' => [
                    [
                        'meta'       => ['update' => true],
                        'type'       => 'customers',
                        'id'         => '<toString(@customer1->id)>',
                        'attributes' => ['name' => 'Updated Customer 1']
                    ],
                    [
                        'meta'       => ['update' => true],
                        'type'       => 'customers',
                        'id'         => '<toString(@customer2->id)>',
                        'attributes' => ['name' => 'Updated Customer 2']
                    ]
                ]
            ]
        );

        $response = $this->cget(
            ['entity' => 'customers'],
            ['fields[customers]' => 'name', 'filter[id][gte]' => '@customer1->id']
        );
        $this->assertResponseContains(
            [
                'data' => [
                    [
                        'type'       => 'customers',
                        'id'         => '<toString(@customer1->id)>',
                        'attributes' => ['name' => 'Updated Customer 1']
                    ],
                    [
                        'type'       => 'customers',
                        'id'         => '<toString(@customer2->id)>',
                        'attributes' => ['name' => 'Updated Customer 2']
                    ]
                ]
            ],
            $response
        );
    }

    public function testCreateAndUpdateEntities()
    {
        $this->processUpdateList(
            Customer::class,
            [
                'data' => [
                    [
                        'type'       => 'customers',
                        'attributes' => ['name' => 'New Customer 1']
                    ],
                    [
                        'meta'       => ['update' => true],
                        'type'       => 'customers',
                        'id'         => '<toString(@customer1->id)>',
                        'attributes' => ['name' => 'Updated Customer 1']
                    ]
                ]
            ]
        );

        $response = $this->cget(
            ['entity' => 'customers'],
            ['fields[customers]' => 'name', 'filter[id][gte]' => '@customer1->id']
        );
        $responseContent = $this->updateResponseContent(
            [
                'data' => [
                    [
                        'type'       => 'customers',
                        'id'         => '<toString(@customer1->id)>',
                        'attributes' => ['name' => 'Updated Customer 1']
                    ],
                    [
                        'type'       => 'customers',
                        'id'         => '<toString(@customer2->id)>',
                        'attributes' => ['name' => 'Customer 2']
                    ],
                    [
                        'type'       => 'customers',
                        'id'         => 'new',
                        'attributes' => ['name' => 'New Customer 1']
                    ]
                ]
            ],
            $response
        );
        $this->assertResponseContains($responseContent, $response);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCreateAndUpdateEntitiesWithIncludes()
    {
        $this->processUpdateList(
            Customer::class,
            [
                'data'     => [
                    [
                        'type'          => 'customers',
                        'attributes'    => ['name' => 'New Customer 1'],
                        'relationships' => [
                            'addresses' => ['data' => [['type' => 'customeraddresses', 'id' => 'ca1']]]
                        ]
                    ],
                    [
                        'meta'          => ['update' => true],
                        'type'          => 'customers',
                        'id'            => '<toString(@customer2->id)>',
                        'relationships' => [
                            'addresses' => ['data' => [['type' => 'customeraddresses', 'id' => 'ca2']]]
                        ]
                    ]
                ],
                'included' => [
                    [
                        'type'          => 'customeraddresses',
                        'id'            => 'ca1',
                        'attributes'    => [
                            'primary'      => true,
                            'label'        => 'Included address1',
                            'street'       => 'test street',
                            'city'         => 'New York',
                            'postalCode'   => '90001',
                            'organization' => 'Acme',
                            'types'        => [
                                ['default' => true, 'addressType' => 'billing'],
                                ['default' => true, 'addressType' => 'shipping']
                            ],
                        ],
                        'relationships' => [
                            'country' => [
                                'data' => ['type' => 'countries', 'id' => '<toString(@country_usa->iso2Code)>']
                            ],
                            'region'  => [
                                'data' => [
                                    'type' => 'regions',
                                    'id'   => '<toString(@region_usa_california->combinedCode)>'
                                ]
                            ]
                        ]
                    ],
                    [
                        'type'          => 'customeraddresses',
                        'id'            => 'ca2',
                        'attributes'    => [
                            'primary'      => true,
                            'label'        => 'Included address2',
                            'street'       => 'test street',
                            'city'         => 'Los Angeles',
                            'postalCode'   => '90210',
                            'organization' => 'Acme',
                            'types'        => [
                                ['default' => true, 'addressType' => 'billing'],
                                ['default' => true, 'addressType' => 'shipping']
                            ]
                        ],
                        'relationships' => [
                            'country' => [
                                'data' => ['type' => 'countries', 'id' => '<toString(@country_usa->iso2Code)>']
                            ],
                            'region'  => [
                                'data' => [
                                    'type' => 'regions',
                                    'id'   => '<toString(@region_usa_california->combinedCode)>'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        );

        $response = $this->cget(
            ['entity' => 'customers'],
            [
                'filter[id][gte]'   => '@customer1->id',
                'fields[customers]' => 'name,addresses',
            ]
        );
        $responseContent = $this->updateResponseContent(
            [
                'data' => [
                    [
                        'type'          => 'customers',
                        'id'            => '<toString(@customer1->id)>',
                        'attributes'    => ['name' => 'Customer 1'],
                        'relationships' => ['addresses' => []]
                    ],
                    [
                        'type'          => 'customers',
                        'id'            => '<toString(@customer2->id)>',
                        'attributes'    => ['name' => 'Customer 2'],
                        'relationships' => [
                            'addresses' => ['data' => [['type' => 'customeraddresses', 'id' => 'new']]]
                        ],
                    ],
                    [
                        'type'          => 'customers',
                        'id'            => 'new',
                        'attributes'    => ['name' => 'New Customer 1'],
                        'relationships' => [
                            'addresses' => ['data' => [['type' => 'customeraddresses', 'id' => 'new']]]
                        ]
                    ]
                ]
            ],
            $response
        );
        $this->assertResponseContains($responseContent, $response);

        $repository = $this->getEntityManager()->getRepository(Customer::class);
        /** @var CustomerAddress $newCustomerAddress */
        $newCustomerAddress = $repository->findOneBy(['name' => 'New Customer 1'])->getAddresses()->first();
        self::assertEquals('Included address1', $newCustomerAddress->getLabel());
        self::assertEquals('New York', $newCustomerAddress->getCity());
        self::assertEquals(2, $newCustomerAddress->getAddressTypes()->count());

        /** @var CustomerAddress $customer2Address */
        $customer2Address = $repository->findOneBy(['name' => 'Customer 2'])->getAddresses()->first();
        self::assertEquals('Included address2', $customer2Address->getLabel());
        self::assertEquals('Los Angeles', $customer2Address->getCity());
        self::assertEquals(2, $customer2Address->getAddressTypes()->count());
    }

    public function testTryToCreateEntitiesWithErrorsInIncludes()
    {
        $operationId = $this->processUpdateList(
            Customer::class,
            [
                'data'     => [
                    [
                        'type'          => 'customers',
                        'attributes'    => ['name' => 'New Customer 1'],
                        'relationships' => [
                            'addresses' => ['data' => [['type' => 'customeraddresses', 'id' => 'ca1']]]
                        ]
                    ]
                ],
                'included' => [
                    [
                        'type'          => 'customeraddresses',
                        'id'            => 'ca1',
                        'attributes'    => [
                            'primary'      => true,
                            'label'        => 'Included address1',
                            'postalCode'   => '90001',
                            'organization' => 'Acme',
                            'types'        => [
                                ['default' => true, 'addressType' => 'billing'],
                                ['default' => true, 'addressType' => 'shipping']
                            ],
                        ],
                        'relationships' => [
                            'country' => [
                                'data' => ['type' => 'countries', 'id' => '<toString(@country_usa->iso2Code)>']
                            ],
                            'region'  => [
                                'data' => [
                                    'type' => 'regions',
                                    'id'   => '<toString(@region_usa_california->combinedCode)>'
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            false
        );

        $this->assertAsyncOperationErrors(
            [
                [
                    'id'     => $operationId . '-1-1',
                    'status' => 400,
                    'title'  => 'not blank constraint',
                    'detail' => 'This value should not be blank.',
                    'source' => ['pointer' => '/included/0/attributes/street']
                ],
                [
                    'id'     => $operationId . '-1-2',
                    'status' => 400,
                    'title'  => 'not blank constraint',
                    'detail' => 'This value should not be blank.',
                    'source' => ['pointer' => '/included/0/attributes/city']
                ]
            ],
            $operationId
        );
    }
}
