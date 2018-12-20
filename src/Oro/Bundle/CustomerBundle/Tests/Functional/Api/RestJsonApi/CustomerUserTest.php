<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api\RestJsonApi;

use Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApiTestCase;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserData;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;

/**
 * @dbIsolationPerTest
 */
class CustomerUserTest extends RestJsonApiTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->loadFixtures([
            LoadOrganization::class,
            LoadCustomerUserData::class
        ]);
        $role = $this->getEntityManager()
            ->getRepository(CustomerUserRole::class)
            ->findOneBy(['role' => 'ROLE_FRONTEND_ADMINISTRATOR']);
        $this->getReferenceRepository()->addReference('ROLE_FRONTEND_ADMINISTRATOR', $role);
    }

    public function testGetList()
    {
        $response = $this->cget(
            ['entity' => 'customerusers'],
            ['filter[email]' => LoadCustomerUserData::EMAIL]
        );

        $this->assertResponseContains('cget_customer_user.yml', $response);
    }

    public function testGet()
    {
        $customerUserId = $this->getReference(LoadCustomerUserData::EMAIL)->getId();
        $response = $this->get(
            ['entity' => 'customerusers', 'id' => $customerUserId]
        );

        $this->assertResponseContains('get_customer_user.yml', $response);
    }

    public function testDelete()
    {
        $customerUserId = $this->getReference(LoadCustomerUserData::EMAIL)->getId();

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
            ['filter[email]' => LoadCustomerUserData::EMAIL]
        );

        $deletedCustomerUser = $this->getEntityManager()
            ->getRepository(CustomerUser::class)
            ->findOneBy(['email' => LoadCustomerUserData::EMAIL]);
        self::assertTrue(null === $deletedCustomerUser);
    }

    public function testCreate()
    {
        $ownerId = $this->getReference('user')->getId();
        $organizationId = $this->getReference('organization')->getId();

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
        self::assertEquals($organizationId, $customerUser->getOrganization()->getId());
        self::assertEquals($ownerId, $customerUser->getOwner()->getId());
    }

    public function testUpdate()
    {
        $customerUserId = $this->getReference(LoadCustomerUserData::EMAIL)->getId();
        $customerId = $this->getReference('customer.level_1.1')->getId();
        $customerUserRoleId = $this->getEntityManager()
            ->getRepository(CustomerUserRole::class)
            ->findOneBy(['role' => 'ROLE_FRONTEND_BUYER'])
            ->getId();

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

    public function testUpdateRelationshipForRoles()
    {
        $customerUserId = $this->getReference(LoadCustomerUserData::EMAIL)->getId();
        $customerUserRoleId = $this->getEntityManager()
            ->getRepository(CustomerUserRole::class)
            ->findOneBy(['role' => 'ROLE_FRONTEND_BUYER'])
            ->getId();

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
        $customerUserId = $this->getReference(LoadCustomerUserData::EMAIL)->getId();
        $customerUserRoleId = $this->getEntityManager()
            ->getRepository(CustomerUserRole::class)
            ->findOneBy(['role' => 'ROLE_FRONTEND_BUYER'])
            ->getId();

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
        $customerUserRoleId = $this->getEntityManager()
            ->getRepository(CustomerUserRole::class)
            ->findOneBy(['role' => 'ROLE_FRONTEND_ADMINISTRATOR'])
            ->getId();
        $customerUser = $this->getReference(LoadCustomerUserData::EMAIL);
        $customerUserId = $customerUser->getId();
        $customerUser->addRole(
            $this->getEntityManager()
                ->getRepository(CustomerUserRole::class)
                ->findOneBy(['role' => 'ROLE_FRONTEND_BUYER'])
        );
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

    public function testDeleteRelationshipForRolesWhenAllUserRolesAreRemoved()
    {
        $customerUserId = $this->getReference(LoadCustomerUserData::EMAIL)->getId();
        $customerUserRoleId = $this->getEntityManager()
            ->getRepository(CustomerUserRole::class)
            ->findOneBy(['role' => 'ROLE_FRONTEND_ADMINISTRATOR'])
            ->getId();

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
}
