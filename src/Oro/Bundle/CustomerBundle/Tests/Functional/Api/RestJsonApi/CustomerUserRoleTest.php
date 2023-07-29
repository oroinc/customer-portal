<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api\RestJsonApi;

use Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApiTestCase;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Tests\Functional\Api\DataFixtures\LoadCustomerUserRoleData;
use Symfony\Component\HttpFoundation\Response;

/**
 * @dbIsolationPerTest
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class CustomerUserRoleTest extends RestJsonApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures([LoadCustomerUserRoleData::class]);
    }

    public function testGetList(): void
    {
        $response = $this->cget(
            ['entity' => 'customeruserroles'],
            ['filter[role]' => 'ROLE_FRONTEND_2']
        );

        $this->assertResponseContains(
            [
                'data' => [
                    [
                        'type'          => 'customeruserroles',
                        'id'            => '<toString(@role2->id)>',
                        'attributes'    => [
                            'role'        => 'ROLE_FRONTEND_2',
                            'label'       => 'Role 2',
                            'selfManaged' => true,
                            'public'      => true
                        ],
                        'relationships' => [
                            'organization'  => [
                                'data' => ['type' => 'organizations', 'id' => '<toString(@organization->id)>']
                            ],
                            'customer'      => [
                                'data' => ['type' => 'customers', 'id' => '<toString(@customer->id)>']
                            ],
                            'customerUsers' => [
                                'data' => [['type' => 'customerusers', 'id' => '<toString(@customer_user->id)>']]
                            ]
                        ]
                    ]
                ]
            ],
            $response
        );
    }

    public function testGet(): void
    {
        $response = $this->get(
            ['entity' => 'customeruserroles', 'id' => '<toString(@role1->id)>']
        );

        $this->assertResponseContains(
            [
                'data' => [
                    'type'          => 'customeruserroles',
                    'id'            => '<toString(@role1->id)>',
                    'attributes'    => [
                        'role'        => 'ROLE_FRONTEND_1',
                        'label'       => 'Role 1',
                        'selfManaged' => false,
                        'public'      => true
                    ],
                    'relationships' => [
                        'organization'  => [
                            'data' => ['type' => 'organizations', 'id' => '<toString(@organization->id)>']
                        ],
                        'customer'      => [
                            'data' => ['type' => 'customers', 'id' => '<toString(@customer->id)>']
                        ],
                        'customerUsers' => [
                            'data' => [['type' => 'customerusers', 'id' => '<toString(@customer_user->id)>']]
                        ]
                    ]
                ]
            ],
            $response
        );
    }

    public function testDelete(): void
    {
        $roleId = $this->getReference('role3')->getId();

        $this->delete(
            ['entity' => 'customeruserroles', 'id' => (string)$roleId]
        );

        $deletedRole = $this->getEntityManager()->find(CustomerUserRole::class, $roleId);
        self::assertTrue(null === $deletedRole);
    }

    public function testTryToDeleteWhenAssignedToSomeUsers(): void
    {
        $response = $this->delete(
            ['entity' => 'customeruserroles', 'id' => '<toString(@role2->id)>'],
            [],
            [],
            false
        );

        $this->assertResponseValidationError(
            [
                'title'  => 'access denied exception',
                'detail' => 'No access by "DELETE" permission to the entity.'
            ],
            $response,
            Response::HTTP_FORBIDDEN
        );
    }

    public function testDeleteList(): void
    {
        $roleId = $this->getReference('role3')->getId();

        $this->cdelete(
            ['entity' => 'customeruserroles'],
            ['filter[role]' => 'ROLE_FRONTEND_3']
        );

        $deletedRole = $this->getEntityManager()->find(CustomerUserRole::class, $roleId);
        self::assertTrue(null === $deletedRole);
    }

    public function testCreate(): void
    {
        $data = [
            'data' => [
                'type'       => 'customeruserroles',
                'attributes' => [
                    'label' => 'New Role'
                ]
            ]
        ];
        $response = $this->post(['entity' => 'customeruserroles'], $data);

        $this->assertResponseContains($data, $response);

        $role = $this->getEntityManager()->find(CustomerUserRole::class, $this->getResourceId($response));
        self::assertNotNull($role);
        self::assertEquals('New Role', $role->getLabel());
    }

    public function testCreateWithCode(): void
    {
        $data = [
            'data' => [
                'type'       => 'customeruserroles',
                'attributes' => [
                    'role'  => 'new 1',
                    'label' => 'New Role'
                ]
            ]
        ];
        $response = $this->post(['entity' => 'customeruserroles'], $data);

        $responseContext = self::jsonToArray($response->getContent());
        $roleCode = $responseContext['data']['attributes']['role'];
        self::assertStringStartsWith(CustomerUserRole::PREFIX_ROLE . 'NEW_1_', $roleCode);

        $expectedData = $data;
        $expectedData['data']['attributes']['role'] = $roleCode;
        $this->assertResponseContains($expectedData, $response);

        $role = $this->getEntityManager()->find(CustomerUserRole::class, $this->getResourceId($response));
        self::assertNotNull($role);
        self::assertEquals($roleCode, $role->getRole());
        self::assertEquals('New Role', $role->getLabel());
    }

    public function testCreateWithPrefixedCode(): void
    {
        $data = [
            'data' => [
                'type'       => 'customeruserroles',
                'attributes' => [
                    'role'  => CustomerUserRole::PREFIX_ROLE . 'NEW_1',
                    'label' => 'New Role'
                ]
            ]
        ];
        $response = $this->post(['entity' => 'customeruserroles'], $data);

        $responseContext = self::jsonToArray($response->getContent());
        $roleCode = $responseContext['data']['attributes']['role'];
        self::assertStringStartsWith(CustomerUserRole::PREFIX_ROLE . 'NEW_1_', $roleCode);

        $expectedData = $data;
        $expectedData['data']['attributes']['role'] = $roleCode;
        $this->assertResponseContains($expectedData, $response);

        $role = $this->getEntityManager()->find(CustomerUserRole::class, $this->getResourceId($response));
        self::assertNotNull($role);
        self::assertEquals($roleCode, $role->getRole());
        self::assertEquals('New Role', $role->getLabel());
    }

    public function testUpdate(): void
    {
        $roleId = $this->getReference('role1')->getId();

        $data = [
            'data' => [
                'type'       => 'customeruserroles',
                'id'         => (string)$roleId,
                'attributes' => [
                    'label' => 'Updated Role'
                ]
            ]
        ];
        $response = $this->patch(['entity' => 'customeruserroles', 'id' => (string)$roleId], $data);

        $role = $this->getEntityManager()->find(CustomerUserRole::class, $this->getResourceId($response));
        self::assertNotNull($role);
        self::assertEquals('Updated Role', $role->getLabel());
    }

    public function testTryToCreateWithoutLabel(): void
    {
        $data = [
            'data' => [
                'type' => 'customeruserroles'
            ]
        ];
        $response = $this->post(['entity' => 'customeruserroles'], $data, [], false);

        $this->assertResponseValidationError(
            [
                'title'  => 'not blank constraint',
                'detail' => 'This value should not be blank.',
                'source' => ['pointer' => '/data/attributes/label']
            ],
            $response
        );
    }

    public function testTryToSetLabelToNull(): void
    {
        $roleId = $this->getReference('role1')->getId();

        $data = [
            'data' => [
                'type'       => 'customeruserroles',
                'id'         => (string)$roleId,
                'attributes' => [
                    'label' => null
                ]
            ]
        ];
        $response = $this->patch(['entity' => 'customeruserroles', 'id' => (string)$roleId], $data, [], false);

        $this->assertResponseValidationErrors(
            [
                [
                    'title'  => 'not blank constraint',
                    'detail' => 'This value should not be blank.',
                    'source' => ['pointer' => '/data/attributes/label']
                ],
                [
                    'title'  => 'length constraint',
                    'detail' => 'This value is too short. It should have 3 characters or more.',
                    'source' => ['pointer' => '/data/attributes/label']
                ]
            ],
            $response
        );
    }

    public function testTryToSetCodeToNull(): void
    {
        $roleId = $this->getReference('role1')->getId();

        $data = [
            'data' => [
                'type'       => 'customeruserroles',
                'id'         => (string)$roleId,
                'attributes' => [
                    'role' => null
                ]
            ]
        ];
        $response = $this->patch(['entity' => 'customeruserroles', 'id' => (string)$roleId], $data, [], false);

        $this->assertResponseValidationError(
            [
                'title'  => 'not blank constraint',
                'detail' => 'This value should not be blank.',
                'source' => ['pointer' => '/data/attributes/role']
            ],
            $response
        );
    }

    public function testChangeCode(): void
    {
        $roleId = $this->getReference('role1')->getId();

        $data = [
            'data' => [
                'type'       => 'customeruserroles',
                'id'         => (string)$roleId,
                'attributes' => [
                    'role' => 'UPDATED1'
                ]
            ]
        ];
        $response = $this->patch(['entity' => 'customeruserroles', 'id' => (string)$roleId], $data);

        $role = $this->getEntityManager()->find(CustomerUserRole::class, $this->getResourceId($response));
        self::assertNotNull($role);
        self::assertStringStartsWith(CustomerUserRole::PREFIX_ROLE . 'UPDATED1_', $role->getRole());
    }
}
