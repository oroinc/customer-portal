<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api\Frontend\RestJsonApi;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Tests\Functional\Api\Frontend\DataFixtures\LoadAdminCustomerUserData;
use Oro\Bundle\FrontendBundle\Tests\Functional\Api\FrontendRestJsonApiTestCase;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Test\Functional\RolePermissionExtension;
use Symfony\Component\HttpFoundation\Response;

/**
 * @dbIsolationPerTest
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class CustomerUserTest extends FrontendRestJsonApiTestCase
{
    use RolePermissionExtension;

    protected function setUp()
    {
        parent::setUp();
        $this->loadFixtures([
            LoadAdminCustomerUserData::class,
            '@OroCustomerBundle/Tests/Functional/Api/Frontend/DataFixtures/customer_user.yml'
        ]);
    }

    public function testGetList()
    {
        $response = $this->cget(['entity' => 'customerusers']);

        $this->assertResponseContains('cget_customer_user.yml', $response);
    }

    public function testGetListFilteredByMineId()
    {
        $response = $this->cget(
            ['entity' => 'customerusers'],
            ['filter' => ['id' => 'mine']]
        );

        $this->assertResponseContains('cget_customer_user_mine.yml', $response);
    }

    public function testGetListFilteredByMineCustomerId()
    {
        $response = $this->cget(
            ['entity' => 'customerusers'],
            ['filter' => ['customer' => 'mine']]
        );

        $this->assertResponseContains('cget_customer_user_mine.yml', $response);
    }

    public function testGet()
    {
        $response = $this->get(
            ['entity' => 'customerusers', 'id' => '<toString(@customer_user1->id)>']
        );

        $this->assertResponseContains('get_customer_user.yml', $response);
    }

    public function testGetByMineId()
    {
        $response = $this->get(
            ['entity' => 'customerusers', 'id' => 'mine']
        );

        $this->assertResponseContains('get_customer_user_mine.yml', $response);
    }

    public function testTryToGetFromAnotherRootCustomer()
    {
        $response = $this->get(
            ['entity' => 'customerusers', 'id' => '<toString(@another_customer_user->id)>'],
            [],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_NOT_FOUND);
    }

    public function testCreate()
    {
        $websiteId = $this->getDefaultWebsiteId();
        $ownerId = $this->getReference('user')->getId();
        $organizationId = $this->getReference('organization')->getId();
        $customerId = $this->getReference('customer')->getId();

        $response = $this->post(
            ['entity' => 'customerusers'],
            'create_customer_user.yml'
        );

        $customerUserId = (int)$this->getResourceId($response);
        $responseContent = $this->updateResponseContent('create_customer_user.yml', $response);
        $this->assertResponseContains($responseContent, $response);

        /** @var CustomerUser $customerUser */
        $customerUser = $this->getEntityManager()
            ->find(CustomerUser::class, $customerUserId);
        self::assertEquals($customerUser->getEmail(), $customerUser->getUsername());
        self::assertEquals($websiteId, $customerUser->getWebsite()->getId());
        self::assertEquals($organizationId, $customerUser->getOrganization()->getId());
        self::assertEquals($ownerId, $customerUser->getOwner()->getId());
        self::assertEquals($customerId, $customerUser->getCustomer()->getId());
    }

    public function testCreateWithRequiredDataOnly()
    {
        $websiteId = $this->getDefaultWebsiteId();
        $ownerId = $this->getReference('user')->getId();
        $organizationId = $this->getReference('organization')->getId();
        $customerId = $this->getReference('customer')->getId();

        $data = $this->getRequestData('create_customer_user_min.yml');
        $response = $this->post(
            ['entity' => 'customerusers'],
            $data
        );

        $customerUserId = (int)$this->getResourceId($response);
        $responseContent = $data;
        unset($responseContent['data']['attributes']['password']);
        $responseContent['data']['relationships']['customer']['data'] = [
            'type' => 'customers',
            'id'   => (string)$customerId
        ];
        $this->assertResponseContains($responseContent, $response);

        /** @var CustomerUser $customerUser */
        $customerUser = $this->getEntityManager()
            ->find(CustomerUser::class, $customerUserId);
        self::assertEquals($customerUser->getEmail(), $customerUser->getUsername());
        self::assertEquals($websiteId, $customerUser->getWebsite()->getId());
        self::assertEquals($organizationId, $customerUser->getOrganization()->getId());
        self::assertEquals($ownerId, $customerUser->getOwner()->getId());
        self::assertEquals($customerId, $customerUser->getCustomer()->getId());
    }

    public function testTryToCreateWithoutData()
    {
        $response = $this->post(
            ['entity' => 'customerusers'],
            ['data' => ['type' => 'customerusers']],
            [],
            false
        );

        $this->assertResponseValidationErrors(
            [
                ['title' => 'not blank constraint', 'source' => ['pointer' => '/data/attributes/email']],
                ['title' => 'not blank constraint', 'source' => ['pointer' => '/data/attributes/firstName']],
                ['title' => 'not blank constraint', 'source' => ['pointer' => '/data/attributes/lastName']],
                ['title' => 'not null constraint', 'source' => ['pointer' => '/data/attributes/password']],
                ['title' => 'not blank constraint', 'source' => ['pointer' => '/data/attributes/password']]
            ],
            $response
        );
    }

    public function testUpdate()
    {
        $customerUserId = $this->getReference('customer_user1')->getId();
        $customerId = $this->getReference('customer')->getId();
        $customerUserRoleId = $this->getReference('buyer')->getId();

        $data = [
            'data' => [
                'type'          => 'customerusers',
                'id'            => (string)$customerUserId,
                'attributes'    => [
                    'firstName' => 'Updated First Name'
                ],
                'relationships' => [
                    'customer' => [
                        'data' => ['type' => 'customers', 'id' => (string)$customerId]
                    ],
                    'roles'    => [
                        'data' => [
                            ['type' => 'customeruserroles', 'id' => (string)$customerUserRoleId]
                        ]
                    ]
                ]
            ]
        ];
        $this->patch(
            ['entity' => 'customerusers', 'id' => $customerUserId],
            $data
        );

        $customerUser = $this->getEntityManager()
            ->find(CustomerUser::class, $customerUserId);
        self::assertEquals('Updated First Name', $customerUser->getFirstName());
        self::assertEquals($customerId, $customerUser->getCustomer()->getId());
        self::assertTrue(null !== $customerUser->getRole('ROLE_FRONTEND_BUYER'));
        self::assertCount(1, $customerUser->getRoles());
    }

    public function testDelete()
    {
        $customerUserId = $this->getReference('customer_user2')->getId();

        $this->delete(
            ['entity' => 'customerusers', 'id' => $customerUserId]
        );

        $deletedCustomerUser = $this->getEntityManager()
            ->find(CustomerUser::class, $customerUserId);
        self::assertTrue(null === $deletedCustomerUser);
    }

    public function testDeleteList()
    {
        $this->cdelete(
            ['entity' => 'customerusers'],
            ['filter[email]' => 'user2@example.com']
        );

        $deletedCustomerUser = $this->getEntityManager()
            ->getRepository(CustomerUser::class)
            ->findOneBy(['email' => 'user2@example.com']);
        self::assertTrue(null === $deletedCustomerUser);
    }

    public function testGetCustomerSubresourceByMineId()
    {
        $response = $this->getSubresource(
            ['entity' => 'customerusers', 'id' => 'mine', 'association' => 'customer']
        );

        $this->assertResponseContains(
            [
                'data' => [
                    'type'       => 'customers',
                    'id'         => '<toString(@customer->id)>',
                    'attributes' => [
                        'name' => 'Customer'
                    ]
                ]
            ],
            $response
        );
    }

    public function testGetCustomerRelationshipByMineId()
    {
        $response = $this->getRelationship(
            ['entity' => 'customerusers', 'id' => 'mine', 'association' => 'customer']
        );

        $this->assertResponseContains(
            [
                'data' => [
                    'type' => 'customers',
                    'id'   => '<toString(@customer->id)>'
                ]
            ],
            $response
        );
    }

    public function testUpdateRelationshipForRoles()
    {
        $customerUserId = $this->getReference('customer_user1')->getId();
        $customerUserRoleId = $this->getReference('buyer')->getId();

        $this->patchRelationship(
            ['entity' => 'customerusers', 'id' => $customerUserId, 'association' => 'roles'],
            [
                'data' => [
                    ['type' => 'customeruserroles', 'id' => (string)$customerUserRoleId]
                ]
            ]
        );

        $customerUser = $this->getEntityManager()
            ->find(CustomerUser::class, $customerUserId);
        self::assertTrue(null !== $customerUser->getRole('ROLE_FRONTEND_BUYER'));
        self::assertCount(1, $customerUser->getRoles());
    }

    public function testAddRelationshipForRoles()
    {
        $customerUserId = $this->getReference('customer_user1')->getId();
        $customerUserRoleId = $this->getReference('buyer')->getId();

        $this->postRelationship(
            ['entity' => 'customerusers', 'id' => $customerUserId, 'association' => 'roles'],
            [
                'data' => [
                    ['type' => 'customeruserroles', 'id' => (string)$customerUserRoleId]
                ]
            ]
        );

        $customerUser = $this->getEntityManager()
            ->find(CustomerUser::class, $customerUserId);
        self::assertTrue(null !== $customerUser->getRole('ROLE_FRONTEND_BUYER'));
        self::assertTrue(null !== $customerUser->getRole('ROLE_FRONTEND_ADMINISTRATOR'));
        self::assertCount(2, $customerUser->getRoles());
    }

    public function testDeleteRelationshipForRoles()
    {
        $customerUserRoleId = $this->getReference('admin')->getId();
        $customerUser = $this->getReference('customer_user1');
        $customerUserId = $customerUser->getId();
        $customerUser->addRole($this->getReference('buyer'));
        $this->getEntityManager()->flush();
        self::assertCount(2, $customerUser->getRoles());
        $this->getEntityManager()->clear();

        $this->deleteRelationship(
            ['entity' => 'customerusers', 'id' => $customerUserId, 'association' => 'roles'],
            [
                'data' => [
                    ['type' => 'customeruserroles', 'id' => (string)$customerUserRoleId]
                ]
            ]
        );

        $customerUser = $this->getEntityManager()
            ->find(CustomerUser::class, $customerUserId);
        self::assertTrue(null !== $customerUser->getRole('ROLE_FRONTEND_BUYER'));
        self::assertCount(1, $customerUser->getRoles());
    }

    public function testTryToSetEmptyRoles()
    {
        $customerUserId = $this->getReference('customer_user1')->getId();

        $data = [
            'data' => [
                'type'          => 'customerusers',
                'id'            => (string)$customerUserId,
                'relationships' => [
                    'roles' => [
                        'data' => []
                    ]
                ]
            ]
        ];
        $response = $this->patch(
            ['entity' => 'customerusers', 'id' => $customerUserId],
            $data,
            [],
            false
        );

        $this->assertResponseValidationError(
            [
                'title'  => 'customer user check role constraint',
                'detail' => 'Please select at least one role before you enable the customer user'
            ],
            $response
        );
    }

    public function testTryToSetEmptyRolesViaUpdateRelationship()
    {
        $customerUserId = $this->getReference('customer_user1')->getId();

        $response = $this->patchRelationship(
            ['entity' => 'customerusers', 'id' => $customerUserId, 'association' => 'roles'],
            ['data' => []],
            [],
            false
        );

        $this->assertResponseValidationError(
            [
                'title'  => 'customer user check role constraint',
                'detail' => 'Please select at least one role before you enable the customer user'
            ],
            $response
        );
    }

    public function testTryToRemoveAllRolesViaDeleteRelationship()
    {
        $customerUserId = $this->getReference('customer_user1')->getId();
        $customerUserRoleId = $this->getReference('admin')->getId();

        $response = $this->deleteRelationship(
            ['entity' => 'customerusers', 'id' => $customerUserId, 'association' => 'roles'],
            [
                'data' => [
                    ['type' => 'customeruserroles', 'id' => (string)$customerUserRoleId]
                ]
            ],
            [],
            false
        );

        $this->assertResponseValidationError(
            [
                'title'  => 'customer user check role constraint',
                'detail' => 'Please select at least one role before you enable the customer user'
            ],
            $response
        );
    }

    public function testTryToCreateWithCustomerFromAnotherDepartment()
    {
        $anotherCustomerId = $this->getReference('another_customer')->getId();

        $data = $this->getRequestData('create_customer_user_min.yml');
        $data['data']['relationships']['customer']['data'] = [
            'type' => 'customers',
            'id'   => (string)$anotherCustomerId
        ];
        $response = $this->post(
            ['entity' => 'customerusers'],
            $data,
            [],
            false
        );

        $this->assertResponseValidationErrors(
            [
                [
                    'title'  => 'frontend owner constraint',
                    'detail' => 'You have no access to set this value as customer.',
                    'source' => ['pointer' => '/data/relationships/customer/data']
                ],
                [
                    'title'  => 'access granted constraint',
                    'detail' => 'The "VIEW" permission is denied for the related resource.',
                    'source' => ['pointer' => '/data/relationships/customer/data']
                ]
            ],
            $response
        );
    }

    public function testTryToSetCustomerFromAnotherDepartment()
    {
        $customerUserId = $this->getReference('customer_user1')->getId();
        $anotherCustomerId = $this->getReference('another_customer')->getId();

        $data = [
            'data' => [
                'type'          => 'customerusers',
                'id'            => (string)$customerUserId,
                'relationships' => [
                    'customer' => [
                        'data' => ['type' => 'customers', 'id' => (string)$anotherCustomerId]
                    ]
                ]
            ]
        ];
        $response = $this->patch(
            ['entity' => 'customerusers', 'id' => $customerUserId],
            $data,
            [],
            false
        );

        $this->assertResponseValidationErrors(
            [
                [
                    'title'  => 'frontend owner constraint',
                    'detail' => 'You have no access to set this value as customer.',
                    'source' => ['pointer' => '/data/relationships/customer/data']
                ],
                [
                    'title'  => 'access granted constraint',
                    'detail' => 'The "VIEW" permission is denied for the related resource.',
                    'source' => ['pointer' => '/data/relationships/customer/data']
                ]
            ],
            $response
        );
    }

    public function testTryToSetCustomerFromAnotherDepartmentViaUpdateCustomerRelationship()
    {
        $customerUserId = $this->getReference('customer_user1')->getId();
        $anotherCustomerId = $this->getReference('another_customer')->getId();

        $data = [
            'data' => ['type' => 'customers', 'id' => (string)$anotherCustomerId]
        ];
        $response = $this->patchRelationship(
            ['entity' => 'customerusers', 'id' => $customerUserId, 'association' => 'customer'],
            $data,
            [],
            false
        );

        $this->assertResponseValidationErrors(
            [
                [
                    'title'  => 'frontend owner constraint',
                    'detail' => 'You have no access to set this value as customer.'
                ],
                [
                    'title'  => 'access granted constraint',
                    'detail' => 'The "VIEW" permission is denied for the related resource.'
                ]
            ],
            $response
        );
    }

    public function testTryToCreateWithChildCustomerWhenCreateAccessLevelIsLocal()
    {
        $this->updateRolePermissions(
            $this->getReference('admin')->getRole(),
            CustomerUser::class,
            [
                'VIEW'   => AccessLevel::DEEP_LEVEL,
                'EDIT'   => AccessLevel::DEEP_LEVEL,
                'ASSIGN' => AccessLevel::DEEP_LEVEL,
                'CREATE' => AccessLevel::LOCAL_LEVEL
            ]
        );

        $childCustomerId = $this->getReference('customer1')->getId();

        $data = $this->getRequestData('create_customer_user_min.yml');
        $data['data']['relationships']['customer']['data'] = [
            'type' => 'customers',
            'id'   => (string)$childCustomerId
        ];
        $response = $this->post(
            ['entity' => 'customerusers'],
            $data,
            [],
            false
        );

        $this->assertResponseValidationError(
            [
                'title'  => 'frontend owner constraint',
                'detail' => 'You have no access to set this value as customer.',
                'source' => ['pointer' => '/data/relationships/customer/data']
            ],
            $response
        );
    }

    public function testTryToSetChildCustomerWhenAssignAccessLevelIsLocal()
    {
        $this->updateRolePermissions(
            $this->getReference('admin')->getRole(),
            CustomerUser::class,
            [
                'VIEW'   => AccessLevel::DEEP_LEVEL,
                'EDIT'   => AccessLevel::DEEP_LEVEL,
                'ASSIGN' => AccessLevel::LOCAL_LEVEL
            ]
        );

        $customerUserId = $this->getReference('customer_user')->getId();
        $childCustomerId = $this->getReference('customer1')->getId();

        $data = [
            'data' => [
                'type'          => 'customerusers',
                'id'            => (string)$customerUserId,
                'relationships' => [
                    'customer' => [
                        'data' => ['type' => 'customers', 'id' => (string)$childCustomerId]
                    ]
                ]
            ]
        ];
        $response = $this->patch(
            ['entity' => 'customerusers', 'id' => $customerUserId],
            $data,
            [],
            false
        );

        $this->assertResponseValidationError(
            [
                'title'  => 'frontend owner constraint',
                'detail' => 'You have no access to set this value as customer.',
                'source' => ['pointer' => '/data/relationships/customer/data']
            ],
            $response
        );
    }

    public function testTryToSetChildCustomerWhenAssignAccessLevelIsLocalViaUpdateCustomerRelationship()
    {
        $this->updateRolePermissions(
            $this->getReference('admin')->getRole(),
            CustomerUser::class,
            [
                'VIEW'   => AccessLevel::DEEP_LEVEL,
                'EDIT'   => AccessLevel::DEEP_LEVEL,
                'ASSIGN' => AccessLevel::LOCAL_LEVEL
            ]
        );

        $customerUserId = $this->getReference('customer_user')->getId();
        $childCustomerId = $this->getReference('customer1')->getId();

        $data = [
            'data' => ['type' => 'customers', 'id' => (string)$childCustomerId]
        ];
        $response = $this->patchRelationship(
            ['entity' => 'customerusers', 'id' => $customerUserId, 'association' => 'customer'],
            $data,
            [],
            false
        );

        $this->assertResponseValidationError(
            [
                'title'  => 'frontend owner constraint',
                'detail' => 'You have no access to set this value as customer.'
            ],
            $response
        );
    }
}
