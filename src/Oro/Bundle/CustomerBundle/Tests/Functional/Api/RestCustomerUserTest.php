<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApiTestCase;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserData;
use Oro\Bundle\UserBundle\DataFixtures\UserUtilityTrait;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group CommunityEdition
 *
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class RestCustomerUserTest extends RestJsonApiTestCase
{
    use UserUtilityTrait;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->loadFixtures([LoadCustomerUserData::class]);
    }

    public function testGetCustomerUsers()
    {
        /** @var CustomerUser $customerUser */
        $customerUser = $this->getReference(LoadCustomerUserData::EMAIL);

        $response = $this->cget(['entity' => $this->getEntityType(CustomerUser::class)]);

        $content = self::jsonToArray($response->getContent());
        $expected = $this->getExpectedData($customerUser);
        $this->assertCount(8, $content['data']);
        $actualCustomerUser = $content['data'][1];
        $this->assertArrayContains($expected, $actualCustomerUser);
    }

    public function testGetCustomerUser()
    {
        /** @var CustomerUser $customerUser */
        $customerUser = $this->getReference(LoadCustomerUserData::EMAIL);
        $expected = $this->getExpectedData($customerUser);

        $response = $this->get(
            ['entity' => $this->getEntityType(CustomerUser::class), 'id' => $customerUser->getId()]
        );

        $content = self::jsonToArray($response->getContent());
        $this->assertArrayContains($expected, $content['data']);
    }

    public function testGetCustomerUserRelations()
    {
        $customerUser = $this->getReference(LoadCustomerUserData::EMAIL);
        $expectedData = $this->getExpectedData($customerUser);
        $relations = [
            'owner',
            'customer',
            'organization',
            'roles',
            'salesRepresentatives'
        ];

        foreach ($relations as $relation) {
            $response = $this->getRelationship([
                'entity' => $this->getEntityType(CustomerUser::class),
                'id' => $customerUser->getId(),
                'association' => $relation
            ]);

            $content = self::jsonToArray($response->getContent());
            $this->assertEquals($expectedData['relationships'][$relation], $content);
        }
    }

    public function testDeleteByFilterCustomerUser()
    {
        $this->markTestSkipped('Should be clarified and fixed in #BB-10944');
        $userName = 'CustomerUserTest';
        $this->createCustomerUser($userName);

        $this->cdelete(
            ['entity' => $this->getEntityType(CustomerUser::class)],
            ['filter' => ['username' => 'CustomerUserTest']]
        );

        $customerUser = $this->getManager()->getRepository(CustomerUser::class)->findOneBy(['username' => $userName]);
        $this->assertNull($customerUser);
    }

    public function testCreateCustomer()
    {
        /** @var CustomerUser $customerUser */
        $customerUser = $this->getReference(LoadCustomerUserData::EMAIL);
        $customer = $customerUser->getCustomer();
        $owner = $customerUser->getOwner();
        $organization = $customerUser->getOrganization();
        $role = $this->getContainer()->get('doctrine')->getRepository(CustomerUserRole::class)->find(1);

        $this->post(
            ['entity' => $this->getEntityType(CustomerUser::class)],
            [
                'data' => [
                    'type' => $this->getEntityType(CustomerUser::class),
                    'attributes' => [
                        'username' => 'test2341@test.com',
                        'password' => '123123123123',
                        'email' => 'test2341@test.com',
                        'firstName' => 'Customer user',
                        'lastName' => 'Customer user'
                    ],
                    'relationships' => [
                        'customer' => [
                            'data' => ['type' => 'customers', 'id' => (string)$customer->getId()]
                        ],
                        'roles' => [
                            'data' => [
                                ['type' => 'customer_user_roles', 'id' => (string)$role->getId()]
                            ]
                        ]
                    ]
                ]
            ]
        );

        $customerUser2 = $this->getManager()
            ->getRepository(CustomerUser::class)
            ->findOneBy(['email' => 'test2341@test.com']);
        $this->assertSame($organization->getId(), $customer->getOrganization()->getId());
        $this->assertSame($customerUser2->getCustomer()->getId(), $customer->getId());
        $this->assertSame($owner->getId(), $customerUser2->getOwner()->getId());

        $this->getManager()->remove($customerUser2);
        $this->getManager()->flush();
    }

    public function testPatchCustomerUser()
    {
        $customerUser = $this->createCustomerUser('testuser');
        $newFirstName = 'new first name';

        $this->patch(
            ['entity' => $this->getEntityType(CustomerUser::class), 'id' => $customerUser->getId()],
            [
                'data' => [
                    'type' => $this->getEntityType(CustomerUser::class),
                    'id' => (string)$customerUser->getId(),
                    'attributes' => [
                        'firstName' => $newFirstName,
                    ]
                ]
            ]
        );

        $customerUser = $this->getManager()->find(CustomerUser::class, $customerUser->getId());
        $this->assertEquals($newFirstName, $customerUser->getFirstName());
        $this->getManager()->remove($customerUser);
        $this->getManager()->flush();
    }

    public function testDeleteCustomerUser()
    {
        $customerUser = $this->createCustomerUser('testuser');

        $this->delete(
            ['entity' => $this->getEntityType(CustomerUser::class), 'id' => $customerUser->getId()]
        );
        $response = $this->get(
            ['entity' => $this->getEntityType(CustomerUser::class), 'id' => $customerUser->getId()],
            [],
            [],
            false
        );
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testPatchCustomer()
    {
        $customerUser = $this->createCustomerUser('testuser');
        $customer = $this->getReference('customer.level_1.1');

        $this->patch(
            ['entity' => $this->getEntityType(CustomerUser::class), 'id' => $customerUser->getId()],
            [
                'data' => [
                    'type' => $this->getEntityType(CustomerUser::class),
                    'id' => (string)$customerUser->getId(),
                    'relationships' => [
                        'customer' => [
                            'data' => ['type' => 'customers', 'id' => (string)$customer->getId()]
                        ]
                    ]
                ]
            ]
        );

        $customerUser = $this->getManager()->find(CustomerUser::class, $customerUser->getId());
        $this->assertEquals($customer->getId(), $customerUser->getCustomer()->getId());
        $this->getManager()->remove($customerUser);
        $this->getManager()->flush();
    }

    public function testPatchRoles()
    {
        $customerUser = $this->createCustomerUser('testuser');
        $customerUserRole = $this->getManager()
            ->getRepository(CustomerUserRole::class)
            ->findOneBy(['role' => 'ROLE_FRONTEND_BUYER']);

        $this->patchRelationship(
            [
                'entity' => $this->getEntityType(CustomerUser::class),
                'id' => $customerUser->getId(),
                'association' => 'roles'
            ],
            [
                'data' => [
                    ['type' => 'customer_user_roles', 'id' => (string)$customerUserRole->getId()]
                ]
            ]
        );

        $customerUser = $this->getManager()->find(CustomerUser::class, $customerUser->getId());
        $this->assertNotNull($customerUser->getRole('ROLE_FRONTEND_BUYER'));
        $this->getManager()->remove($customerUser);
        $this->getManager()->flush();
    }

    public function testPostRoles()
    {
        $customerUser = $this->createCustomerUser('testuser2');
        $customerUserRole = $this->getManager()
            ->getRepository(CustomerUserRole::class)
            ->findOneBy(['role' => 'ROLE_FRONTEND_BUYER']);

        $this->postRelationship(
            [
                'entity' => $this->getEntityType(CustomerUser::class),
                'id' => $customerUser->getId(),
                'association' => 'roles'
            ],
            [
                'data' => [
                    ['type' => 'customer_user_roles', 'id' => (string)$customerUserRole->getId()]
                ]
            ]
        );

        $customerUser = $this->getManager()->find(CustomerUser::class, $customerUser->getId());
        $this->assertNotNull($customerUser->getRole('ROLE_FRONTEND_BUYER'));
        $this->getManager()->remove($customerUser);
        $this->getManager()->flush();
    }

    public function testDeleteRoles()
    {
        $customerUser = $this->createCustomerUser('testuser3');
        $roleAdmin = $this->getManager()
            ->getRepository(CustomerUserRole::class)
            ->findOneBy(['role' => 'ROLE_FRONTEND_ADMINISTRATOR']);
        $roleBuyer = $this->getManager()
            ->getRepository(CustomerUserRole::class)
            ->findOneBy(['role' => 'ROLE_FRONTEND_BUYER']);

        $this->postRelationship(
            [
                'entity' => $this->getEntityType(CustomerUser::class),
                'id' => $customerUser->getId(),
                'association' => 'roles'
            ],
            [
                'data' => [
                    ['type' => 'customer_user_roles', 'id' => (string)$roleAdmin->getId()],
                    ['type' => 'customer_user_roles', 'id' => (string)$roleBuyer->getId()]
                ]
            ]
        );

        $this->deleteRelationship(
            [
                'entity' => $this->getEntityType(CustomerUser::class),
                'id' => $customerUser->getId(),
                'association' => 'roles'
            ],
            [
                'data' => [
                    ['type' => 'customer_user_roles', 'id' => (string)$roleBuyer->getId()]
                ]
            ]
        );

        $customerUser = $this->getManager()->find(CustomerUser::class, $customerUser->getId());
        $this->assertNull($customerUser->getRole('ROLE_FRONTEND_BUYER'));
        $this->getManager()->remove($customerUser);
        $this->getManager()->flush();
    }

    /**
     * @param CustomerUser $customerUser
     * @return array
     */
    protected function getExpectedData(CustomerUser $customerUser)
    {
        $customerUserRole = $this->getManager()
            ->getRepository(CustomerUserRole::class)
            ->findOneBy(['role' => 'ROLE_FRONTEND_ADMINISTRATOR']);
        $createdAt = $customerUser->getCreatedAt();
        $updatedAt = $customerUser->getUpdatedAt();
        $passwordRequestedAt = $customerUser->getPasswordRequestedAt();
        $passwordChangedAt = $customerUser->getPasswordChangedAt();
        $expected = [
            'type' => 'customer_users',
            'id' => (string)$customerUser->getId(),
            'attributes' => [
                "confirmed" => $customerUser->isConfirmed(),
                "email" => $customerUser->getEmail(),
                "namePrefix" => $customerUser->getNamePrefix(),
                "firstName" => $customerUser->getFirstName(),
                "middleName" => $customerUser->getMiddleName(),
                "lastName" => $customerUser->getLastName(),
                "nameSuffix" => $customerUser->getNameSuffix(),
                "birthday" => $customerUser->getBirthday(),
                "createdAt" => $createdAt ? $createdAt->format('Y-m-d\TH:i:s\Z') : null,
                "updatedAt" => $updatedAt ? $updatedAt->format('Y-m-d\TH:i:s\Z') : null,
                "username" => $customerUser->getUsername(),
                "lastLogin" => $customerUser->getLastLogin(),
                "loginCount" => $customerUser->getLoginCount(),
                "enabled" => $customerUser->isEnabled(),
                "passwordRequestedAt" => $passwordRequestedAt ? $passwordRequestedAt->format('Y-m-d\TH:i:s\Z') : null,
                "passwordChangedAt" => $passwordChangedAt ? $passwordChangedAt->format('Y-m-d\TH:i:s\Z') : null,
            ],
            'relationships' => [
                "owner" => [
                    "data" => [
                        "type" => "users",
                        "id" => (string)$customerUser->getOwner()->getId()
                    ]
                ],
                "salesRepresentatives" => [
                    "data" => []
                ],
                "organization" => [
                    "data" => [
                        "type" => "organizations",
                        "id" => (string)$customerUser->getOrganization()->getId()
                    ]
                ],
                'customer' => [
                    'data' => [
                        'type' => 'customers',
                        'id' => (string)$customerUser->getCustomer()->getId()
                    ]
                ],
                'roles' => [
                    'data' => [
                        [
                            'type' => 'customer_user_roles',
                            'id' => (string)$customerUserRole->getId()
                        ]
                    ]
                ]
            ]
        ];

        return $expected;
    }

    /**
     * @param $name
     * @return CustomerUser
     */
    protected function createCustomerUser($name)
    {
        $manager = $this->getManager();
        $owner = $this->getFirstUser($manager);
        $role = $manager->getRepository(CustomerUserRole::class)->findOneBy([
            'role' => 'ROLE_FRONTEND_ADMINISTRATOR'
        ]);

        $customerUser = new CustomerUser();
        $customerUser->setOwner($owner);
        $customerUser->setUsername($name);
        $customerUser->setEmail($name);
        $customerUser->setFirstName('name');
        $customerUser->setLastName('surname');
        $customerUser->setEmail($name.'@test.com');
        $customerUser->addRole($role);
        $customerUser->setPassword('test');
        $manager->persist($customerUser);
        $manager->flush($customerUser);

        return $customerUser;
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectManager|EntityManager
     */
    protected function getManager()
    {
        return $this->getContainer()->get('doctrine')->getManager();
    }
}
