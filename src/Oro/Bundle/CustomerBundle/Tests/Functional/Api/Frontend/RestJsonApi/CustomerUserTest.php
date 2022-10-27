<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api\Frontend\RestJsonApi;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Tests\Functional\Api\Frontend\DataFixtures\LoadAdminCustomerUserData;
use Oro\Bundle\FrontendBundle\Tests\Functional\Api\FrontendRestJsonApiTestCase;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Test\Functional\RolePermissionExtension;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

/**
 * @dbIsolationPerTest
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class CustomerUserTest extends FrontendRestJsonApiTestCase
{
    use RolePermissionExtension;

    protected function setUp(): void
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
        $this->assertResponseNotHasAttributes(
            [
                'password',
                'plainPassword',
                'salt',
                'confirmationToken',
                'emailLowercase',
                'username',
                'passwordChangedAt',
                'passwordRequestedAt',
                'isGuest',
                'lastLogin',
                'loginCount'
            ],
            $response
        );
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
        $this->assertResponseValidationError(
            [
                'title'  => 'access denied exception',
                'detail' => 'No access to the entity.'
            ],
            $response,
            Response::HTTP_FORBIDDEN
        );
    }

    public function testCreate()
    {
        $websiteId = $this->getDefaultWebsiteId();
        $ownerId = $this->getReference('user')->getId();
        $organizationId = $this->getReference('organization')->getId();
        $customerId = $this->getReference('customer')->getId();

        $data = $this->getRequestData('create_customer_user.yml');
        $response = $this->post(
            ['entity' => 'customerusers'],
            $data
        );

        $customerUserId = (int)$this->getResourceId($response);
        $responseContent = $this->updateResponseContent('create_customer_user.yml', $response);
        $this->assertResponseContains($responseContent, $response);

        /** @var CustomerUser $customerUser */
        $customerUser = $this->getEntityManager()
            ->find(CustomerUser::class, $customerUserId);
        self::assertEquals($data['data']['attributes']['firstName'], $customerUser->getFirstName());
        self::assertEquals($data['data']['attributes']['lastName'], $customerUser->getLastName());
        self::assertEquals($data['data']['attributes']['email'], $customerUser->getEmail());
        self::assertEquals($customerUser->getEmail(), $customerUser->getUsername());
        self::assertEquals($websiteId, $customerUser->getWebsite()->getId());
        self::assertEquals($organizationId, $customerUser->getOrganization()->getId());
        self::assertEquals($ownerId, $customerUser->getOwner()->getId());
        self::assertEquals($customerId, $customerUser->getCustomer()->getId());

        self::assertEmpty($customerUser->getPlainPassword());
        self::assertNotEmpty($customerUser->getPassword());
        self::assertNotEmpty($customerUser->getSalt());
        /** @var PasswordEncoderInterface $passwordEncoder */
        $passwordEncoder = self::getContainer()->get('security.encoder_factory')->getEncoder($customerUser);
        self::assertTrue(
            $passwordEncoder->isPasswordValid(
                $customerUser->getPassword(),
                $data['data']['attributes']['password'],
                $customerUser->getSalt()
            )
        );
    }

    public function testCreateWithRequiredDataOnly()
    {
        $websiteId = $this->getDefaultWebsiteId();
        $ownerId = $this->getReference('user')->getId();
        $organizationId = $this->getReference('organization')->getId();
        $customerId = $this->getReference('customer')->getId();
        $customerUserRoleId = $this->getReference('buyer')->getId();

        $data = $this->getRequestData('create_customer_user_min.yml');
        $response = $this->post(['entity' => 'customerusers'], $data);

        $customerUserId = (int)$this->getResourceId($response);
        $responseContent = $data;
        $responseContent['data']['relationships']['customer']['data'] = [
            'type' => 'customers',
            'id'   => (string)$customerId
        ];
        $responseContent['data']['relationships']['userRoles']['data'][] = [
            'type' => 'customeruserroles',
            'id'   => (string)$customerUserRoleId
        ];
        $this->assertResponseContains($responseContent, $response);

        /** @var CustomerUser $customerUser */
        $customerUser = $this->getEntityManager()->find(CustomerUser::class, $customerUserId);
        self::assertEquals($data['data']['attributes']['firstName'], $customerUser->getFirstName());
        self::assertEquals($data['data']['attributes']['lastName'], $customerUser->getLastName());
        self::assertEquals($data['data']['attributes']['email'], $customerUser->getEmail());
        self::assertEquals($customerUser->getEmail(), $customerUser->getUsername());
        self::assertEquals($websiteId, $customerUser->getWebsite()->getId());
        self::assertEquals($organizationId, $customerUser->getOrganization()->getId());
        self::assertEquals($ownerId, $customerUser->getOwner()->getId());
        self::assertEquals($customerId, $customerUser->getCustomer()->getId());
        self::assertCount(1, $customerUser->getUserRoles());
        self::assertEquals($customerUserRoleId, $customerUser->getUserRoles()[0]->getId());

        self::assertEmpty($customerUser->getPlainPassword());
        self::assertNotEmpty($customerUser->getPassword());
        self::assertNotEmpty($customerUser->getSalt());
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
                ['title' => 'not blank constraint', 'source' => ['pointer' => '/data/attributes/lastName']]
            ],
            $response
        );
    }

    public function testCreateWithNullPassword()
    {
        $data = $this->getRequestData('create_customer_user_min.yml');
        $data['data']['attributes']['password'] = null;
        $response = $this->post(
            ['entity' => 'customerusers'],
            $data
        );

        /** @var CustomerUser $customerUser */
        $customerUser = $this->getEntityManager()
            ->find(CustomerUser::class, (int)$this->getResourceId($response));

        self::assertEmpty($customerUser->getPlainPassword());
        self::assertNotEmpty($customerUser->getPassword());
        self::assertNotEmpty($customerUser->getSalt());
    }

    public function testCreateWithEmptyPassword()
    {
        $data = $this->getRequestData('create_customer_user_min.yml');
        $data['data']['attributes']['password'] = '';
        $response = $this->post(
            ['entity' => 'customerusers'],
            $data
        );

        /** @var CustomerUser $customerUser */
        $customerUser = $this->getEntityManager()
            ->find(CustomerUser::class, (int)$this->getResourceId($response));

        self::assertEmpty($customerUser->getPlainPassword());
        self::assertNotEmpty($customerUser->getPassword());
        self::assertNotEmpty($customerUser->getSalt());
    }

    public function testTryToCreateWithInvalidPassword()
    {
        $data = $this->getRequestData('create_customer_user_min.yml');
        $data['data']['attributes']['password'] = '1';
        $response = $this->post(
            ['entity' => 'customerusers'],
            $data,
            [],
            false
        );

        $this->assertResponseValidationError(
            [
                'title'  => 'password complexity constraint',
                'source' => ['pointer' => '/data/attributes/password']
            ],
            $response
        );
    }

    public function testTryToCreateWithInvalidEmail(): void
    {
        $data = $this->getRequestData('create_customer_user_with_invalid_email.yml');
        $data['data']['attributes']['password'] = 'Admin123';
        $response = $this->post(
            ['entity' => 'customerusers'],
            $data,
            [],
            false
        );

        $this->assertResponseValidationError(
            [
                'title'  => 'email constraint',
                'detail' => 'This value is not a valid email address.',
                'source' => ['pointer' => '/data/attributes/email']
            ],
            $response
        );
    }

    public function testTryToCreateWithEnabledAndConfirmedFields()
    {
        $data = $this->getRequestData('create_customer_user_min.yml');
        $data['data']['attributes']['enabled'] = false;
        $data['data']['attributes']['confirmed'] = false;
        $response = $this->post(
            ['entity' => 'customerusers'],
            $data
        );

        $customerUser = $this->getEntityManager()
            ->find(CustomerUser::class, (int)$this->getResourceId($response));
        self::assertTrue($customerUser->isEnabled());
        self::assertTrue($customerUser->isConfirmed());
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
                    'userRoles'    => [
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
        self::assertTrue($customerUser->hasRole('ROLE_FRONTEND_BUYER'));
        self::assertCount(1, $customerUser->getUserRoles());
    }

    public function testTryToUpdateEnabledAndConfirmedFields()
    {
        $customerUserId = $this->getReference('customer_user1')->getId();

        $this->patch(
            ['entity' => 'customerusers', 'id' => $customerUserId],
            [
                'data' => [
                    'type'       => 'customerusers',
                    'id'         => (string)$customerUserId,
                    'attributes' => [
                        'enabled'   => false,
                        'confirmed' => false
                    ]
                ]
            ]
        );

        $customerUser = $this->getEntityManager()
            ->find(CustomerUser::class, $customerUserId);
        self::assertTrue($customerUser->isEnabled());
        self::assertTrue($customerUser->isConfirmed());
    }

    public function testTryToUpdateCurrentLoggedInUserWhenDataAreInvalid()
    {
        /** @var CustomerUser $customerUser */
        $customerUser = $this->getEntityManager()
            ->getRepository(CustomerUser::class)
            ->findOneBy(['email' => self::USER_NAME]);
        $customerUserId = $customerUser->getId();
        $customerUserEmail = $customerUser->getEmail();

        // do not use patch() method to prevent clearing of the entity manager
        // and as result refreshing the security context
        $response = $this->request(
            'PATCH',
            $this->getUrl($this->getItemRouteName(), ['entity' => 'customerusers', 'id' => $customerUserId]),
            [
                'data' => [
                    'type'       => 'customerusers',
                    'id'         => (string)$customerUserId,
                    'attributes' => ['email' => null]
                ]
            ]
        );

        $this->assertResponseValidationError(
            [
                'title'  => 'not blank constraint',
                'source' => ['pointer' => '/data/attributes/email']
            ],
            $response
        );

        /** @var CustomerUser $loggedInCustomerUser */
        $loggedInCustomerUser = self::getContainer()->get('security.token_storage')->getToken()->getUser();
        self::assertSame($customerUserId, $loggedInCustomerUser->getId());
        self::assertSame($customerUserEmail, $loggedInCustomerUser->getEmail());
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

    public function testTryToDeleteCurrentLoggedInUser()
    {
        $customerUserId = $this->getReference('customer_user')->getId();

        $response = $this->delete(
            ['entity' => 'customerusers', 'id' => $customerUserId],
            [],
            [],
            false
        );
        $this->assertResponseValidationError(
            [
                'title'  => 'access denied exception',
                'detail' => 'The delete operation is forbidden. Reason: self delete.'
            ],
            $response,
            Response::HTTP_FORBIDDEN
        );
    }

    public function testTryToDeleteListForCurrentLoggedInUser()
    {
        $response = $this->cdelete(
            ['entity' => 'customerusers'],
            ['filter[id]' => '<toString(@customer_user->id)>'],
            [],
            false
        );
        $this->assertResponseValidationError(
            [
                'title'  => 'access denied exception',
                'detail' => 'The delete operation is forbidden. Reason: self delete.'
            ],
            $response,
            Response::HTTP_FORBIDDEN
        );
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
            ['entity' => 'customerusers', 'id' => $customerUserId, 'association' => 'userRoles'],
            [
                'data' => [
                    ['type' => 'customeruserroles', 'id' => (string)$customerUserRoleId]
                ]
            ]
        );

        $customerUser = $this->getEntityManager()
            ->find(CustomerUser::class, $customerUserId);
        self::assertTrue($customerUser->hasRole('ROLE_FRONTEND_BUYER'));
        self::assertCount(1, $customerUser->getUserRoles());
    }

    public function testAddRelationshipForRoles()
    {
        $customerUserId = $this->getReference('customer_user1')->getId();
        $customerUserRoleId = $this->getReference('buyer')->getId();

        $this->postRelationship(
            ['entity' => 'customerusers', 'id' => $customerUserId, 'association' => 'userRoles'],
            [
                'data' => [
                    ['type' => 'customeruserroles', 'id' => (string)$customerUserRoleId]
                ]
            ]
        );

        $customerUser = $this->getEntityManager()
            ->find(CustomerUser::class, $customerUserId);
        self::assertTrue($customerUser->hasRole('ROLE_FRONTEND_BUYER'));
        self::assertTrue($customerUser->hasRole('ROLE_FRONTEND_ADMINISTRATOR'));
        self::assertCount(2, $customerUser->getUserRoles());
    }

    public function testDeleteRelationshipForRoles()
    {
        $customerUserRoleId = $this->getReference('admin')->getId();
        $customerUser = $this->getReference('customer_user1');
        $customerUserId = $customerUser->getId();
        $customerUser->addUserRole($this->getReference('buyer'));
        $this->getEntityManager()->flush();
        self::assertCount(2, $customerUser->getUserRoles());
        $this->getEntityManager()->clear();

        $this->deleteRelationship(
            ['entity' => 'customerusers', 'id' => $customerUserId, 'association' => 'userRoles'],
            [
                'data' => [
                    ['type' => 'customeruserroles', 'id' => (string)$customerUserRoleId]
                ]
            ]
        );

        $customerUser = $this->getEntityManager()
            ->find(CustomerUser::class, $customerUserId);
        self::assertTrue($customerUser->hasRole('ROLE_FRONTEND_BUYER'));
        self::assertCount(1, $customerUser->getUserRoles());
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
