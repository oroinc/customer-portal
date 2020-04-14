<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api\RestJsonApi;

use Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApiUpdateListTestCase;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Tests\Functional\Api\Frontend\DataFixtures\LoadWebsiteData;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserData;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;

/**
 * @group CommunityEdition
 *
 * @dbIsolationPerTest
 */
class CustomerUserUpdateListTest extends RestJsonApiUpdateListTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures(
            [
                LoadOrganization::class,
                LoadCustomerUserData::class,
                LoadWebsiteData::class,
            ]
        );
        $role = $this->getEntityManager()
            ->getRepository(CustomerUserRole::class)
            ->findOneBy(['role' => 'ROLE_FRONTEND_ADMINISTRATOR']);
        $this->getReferenceRepository()->addReference('ROLE_FRONTEND_ADMINISTRATOR', $role);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCreateEntities()
    {
        $this->processUpdateList(
            CustomerUser::class,
            [
                'data'     => [
                    [
                        'type'          => 'customerusers',
                        'attributes'    => [
                            'email'     => 'test1@test.com',
                            'firstName' => 'Test 1',
                            'lastName'  => 'Doe'
                        ],
                        'relationships' => [
                            'customer' => [
                                'data' => [
                                    'type' => 'customers',
                                    'id'   => 'cu1'
                                ]
                            ]
                        ]
                    ],
                    [
                        'type'          => 'customerusers',
                        'attributes'    => [
                            'email'     => 'test2@test.com',
                            'firstName' => 'Test 2',
                            'lastName'  => 'Doe'
                        ],
                        'relationships' => [
                            'customer' => [
                                'data' => [
                                    'type' => 'customers',
                                    'id'   => 'cu2'
                                ]
                            ]
                        ]
                    ]
                ],
                'included' => [
                    [
                        'type'       => 'customers',
                        'id'         => 'cu1',
                        'attributes' => ['name' => 'New Customer 1'],
                    ],
                    [
                        'type'       => 'customers',
                        'id'         => 'cu2',
                        'attributes' => ['name' => 'New Customer 2']
                    ]
                ]
            ]
        );

        $response = $this->cget(
            ['entity' => 'customerusers'],
            [
                'fields[customerusers]' => 'email,firstName,lastName,customer',
                'filter[id][gt]'        => '@Ryan1Range@example.org->id'
            ]
        );
        $responseContent = $this->updateResponseContent(
            [
                'data' => [
                    [
                        'type'          => 'customerusers',
                        'id'            => 'new',
                        'attributes'    => [
                            'email'     => 'test1@test.com',
                            'firstName' => 'Test 1',
                            'lastName'  => 'Doe'
                        ],
                        'relationships' => [
                            'customer' => [
                                'data' => [
                                    'type' => 'customers',
                                    'id'   => 'new'
                                ]
                            ]
                        ]
                    ],
                    [
                        'type'          => 'customerusers',
                        'id'            => 'new',
                        'attributes'    => [
                            'email'     => 'test2@test.com',
                            'firstName' => 'Test 2',
                            'lastName'  => 'Doe'
                        ],
                        'relationships' => [
                            'customer' => [
                                'data' => [
                                    'type' => 'customers',
                                    'id'   => 'new'
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            $response
        );
        $this->assertResponseContains($responseContent, $response);

        $repository = $this->getEntityManager()->getRepository(CustomerUser::class);

        $customerUser1Customer = $repository->findOneBy(['email' => 'test1@test.com'])->getCustomer();
        self::assertEquals('New Customer 1', $customerUser1Customer->getName());

        $customerUser2Customer = $repository->findOneBy(['email' => 'test2@test.com'])->getCustomer();
        self::assertEquals('New Customer 2', $customerUser2Customer->getName());
    }

    public function testUpdateEntities()
    {
        $this->processUpdateList(
            CustomerUser::class,
            [
                'data' => [
                    [
                        'meta'       => ['update' => true],
                        'type'       => 'customerusers',
                        'id'         => '<toString(@Ryan1Range@example.org->id)>',
                        'attributes' => ['firstName' => 'User 1']
                    ],
                    [
                        'meta'       => ['update' => true],
                        'type'       => 'customerusers',
                        'id'         => '<toString(@customer.level_1.2@test.com->id)>',
                        'attributes' => ['firstName' => 'User 2']
                    ]
                ]
            ]
        );

        $response = $this->cget(
            ['entity' => 'customerusers'],
            ['fields[customers]' => 'firstName', 'filter[id][gte]' => '@customer.level_1.2@test.com->id']
        );
        $this->assertResponseContains(
            [
                'data' => [
                    [
                        'type'       => 'customerusers',
                        'id'         => '<toString(@customer.level_1.2@test.com->id)>',
                        'attributes' => ['firstName' => 'User 2']
                    ],
                    [
                        'type'       => 'customerusers',
                        'id'         => '<toString(@Ryan1Range@example.org->id)>',
                        'attributes' => ['firstName' => 'User 1']
                    ]
                ]
            ],
            $response
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCreateAndUpdateEntities()
    {
        $this->processUpdateList(
            CustomerUser::class,
            [
                'data'     => [
                    [
                        'type'          => 'customerusers',
                        'attributes'    => [
                            'email'     => 'test1@test.com',
                            'firstName' => 'Test 1',
                            'lastName'  => 'Doe'
                        ],
                        'relationships' => [
                            'customer' => [
                                'data' => [
                                    'type' => 'customers',
                                    'id'   => 'cu1'
                                ]
                            ]
                        ]
                    ],
                    [
                        'meta'          => ['update' => true],
                        'type'          => 'customerusers',
                        'id'            => '<toString(@Ryan1Range@example.org->id)>',
                        'attributes'    => ['firstName' => 'Updated User 1'],
                        'relationships' => [
                            'customer' => [
                                'data' => [
                                    'type' => 'customers',
                                    'id'   => 'cu2'
                                ]
                            ]
                        ]
                    ]
                ],
                'included' => [
                    [
                        'type'       => 'customers',
                        'id'         => 'cu1',
                        'attributes' => ['name' => 'New Customer 1']
                    ],
                    [
                        'type'       => 'customers',
                        'id'         => 'cu2',
                        'attributes' => ['name' => 'New Customer 2']
                    ]
                ]
            ]
        );

        $response = $this->cget(
            ['entity' => 'customerusers'],
            [
                'fields[customerusers]' => 'email,firstName,lastName,customer',
                'filter[id][gte]'       => '@Ryan1Range@example.org->id'
            ]
        );
        $responseContent = $this->updateResponseContent(
            [
                'data' => [
                    [
                        'type'          => 'customerusers',
                        'id'            => 'new',
                        'attributes'    => [
                            'email'     => 'Ryan1Range@example.org',
                            'firstName' => 'Updated User 1',
                            'lastName'  => 'Range'
                        ],
                        'relationships' => [
                            'customer' => [
                                'data' => [
                                    'type' => 'customers',
                                    'id'   => 'new'
                                ]
                            ]
                        ]
                    ],
                    [
                        'type'          => 'customerusers',
                        'id'            => 'new',
                        'attributes'    => [
                            'email'     => 'test1@test.com',
                            'firstName' => 'Test 1',
                            'lastName'  => 'Doe'
                        ],
                        'relationships' => [
                            'customer' => [
                                'data' => [
                                    'type' => 'customers',
                                    'id'   => 'new'
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            $response
        );
        $this->assertResponseContains($responseContent, $response);

        $repository = $this->getEntityManager()->getRepository(CustomerUser::class);

        $customerUser1Customer = $repository->findOneBy(['email' => 'test1@test.com'])->getCustomer();
        self::assertEquals('New Customer 1', $customerUser1Customer->getName());

        $ryan1RangCustomer = $repository->findOneBy(['email' => 'Ryan1Range@example.org'])->getCustomer();
        self::assertEquals('New Customer 2', $ryan1RangCustomer->getName());
    }

    public function testTryToCreateEntitiesWithErrors()
    {
        $operationId = $this->processUpdateList(
            CustomerUser::class,
            [
                'data'     => [
                    [
                        'type'          => 'customerusers',
                        'attributes'    => [
                            'email'    => 'test1@test.com',
                            'lastName' => 'Doe'
                        ],
                        'relationships' => [
                            'customer' => [
                                'data' => [
                                    'type' => 'customers',
                                    'id'   => 'cu1'
                                ]
                            ]
                        ]
                    ]
                ],
                'included' => [
                    [
                        'type'       => 'customers',
                        'id'         => 'cu1',
                        'attributes' => ['name' => null]
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
                    'source' => ['pointer' => '/data/0/attributes/firstName']
                ],
                [
                    'id'     => $operationId . '-1-2',
                    'status' => 400,
                    'title'  => 'not blank constraint',
                    'detail' => 'This value should not be blank.',
                    'source' => ['pointer' => '/included/0/attributes/name']
                ]
            ],
            $operationId
        );
    }
}
