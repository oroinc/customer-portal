<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api\RestJsonApi;

use Doctrine\Common\Collections\Collection;
use Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApiTestCase;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Tests\Functional\Api\DataFixtures\LoadCustomerData;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadGroups;
use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * @dbIsolationPerTest
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class CustomerTest extends RestJsonApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures([LoadCustomerData::class]);
        $this->getReferenceRepository()
            ->setReference('default_customer', $this->getDefaultCustomer());
        $this->getReferenceRepository()
            ->setReference('default_customer_user', $this->getDefaultCustomer()->getUsers()->first());
        $role = $this->getEntityManager()
            ->getRepository(CustomerUserRole::class)
            ->findOneBy(['role' => 'ROLE_FRONTEND_ADMINISTRATOR']);
        $this->getReferenceRepository()
            ->setReference('ROLE_FRONTEND_ADMINISTRATOR', $role);
    }

    /**
     * @param string        $name
     * @param string|null   $ratingId
     * @param Customer|null $parent
     *
     * @return Customer
     */
    private function createCustomer($name, $ratingId = null, Customer $parent = null)
    {
        $manager = $this->getEntityManager();
        $owner = $this->getReference('user');

        if (null === $parent) {
            $parent = $manager->getRepository(Customer::class)->findOneByName('CustomerUser CustomerUser');
            // initialize customer users collection to avoid exception about cascade persist operation
            $parent->getUsers()->current();
        }

        $customer = new Customer();
        $customer
            ->setName($name)
            ->setOwner($owner)
            ->setOrganization($owner->getOrganization())
            ->setParent($parent)
            ->addSalesRepresentative($owner);

        if ($ratingId) {
            /** @var AbstractEnumValue $rating */
            $rating = $this->getEntityManager()
                ->find(ExtendHelper::buildEnumValueClassName(Customer::INTERNAL_RATING_CODE), $ratingId);
            $customer->setInternalRating($rating);
        }

        $manager->persist($customer);
        $manager->flush();

        return $customer;
    }

    /**
     * @param string        $email
     * @param Customer|null $customer
     *
     * @return CustomerUser
     */
    private function createCustomerUser($email, Customer $customer = null)
    {
        $role = $this->getEntityManager()
            ->getRepository(CustomerUserRole::class)
            ->findOneBy(['role' => 'ROLE_FRONTEND_ADMINISTRATOR']);

        $customerUser = new CustomerUser();
        $customerUser
            ->setFirstName($email)
            ->setLastName($email)
            ->setEmail($email)
            ->addUserRole($role)
            ->setEnabled(true)
            ->setPlainPassword($email)
            ->setPassword($email);

        if ($customer) {
            $customer->addUser($customerUser);
        }

        $this->getEntityManager()->persist($customerUser);
        $this->getEntityManager()->flush();

        return $customerUser;
    }

    /**
     * @param string $username
     *
     * @return User
     */
    private function createUser($username)
    {
        $organization = $this->getReference('organization');
        $user = new User();
        $user->setUsername($username)
            ->setPlainPassword($username)
            ->setPassword($username)
            ->setEmail($username . '@example.com')
            ->setFirstName('John')
            ->setLastName('Doo')
            ->setOrganization($organization)
            ->addOrganization($organization)
            ->setEnabled(true);

        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();

        return $user;
    }

    /**
     * @return Customer|null
     */
    private function getDefaultCustomer()
    {
        return $this->getEntityManager()
            ->getRepository(Customer::class)
            ->findOneByName('CustomerUser CustomerUser');
    }

    /**
     * @param string $name
     *
     * @return CustomerGroup|null
     */
    private function getGroup($name)
    {
        return $this->getEntityManager()
            ->getRepository(CustomerGroup::class)
            ->findOneByName($name);
    }

    /**
     * @param mixed               $needleId
     * @param Collection|object[] $haystack
     */
    private function assertContainsById($needleId, $haystack)
    {
        if ($haystack instanceof Collection) {
            $haystack = $haystack->toArray();
        }

        $haystack = array_map(
            function ($item) {
                return $item->getId();
            },
            $haystack
        );

        self::assertContains($needleId, $haystack);
    }

    public function testGetList()
    {
        $response = $this->cget(['entity' => 'customers']);
        $this->assertResponseContains('cget_customer.yml', $response);
    }

    public function testGetListFilteredByCreatedAt()
    {
        $customer = $this->createCustomer('customer created now');
        $this->getEntityManager()->clear();

        $response = $this->cget(
            ['entity' => 'customers'],
            ['filter[createdAt][gte]' => $customer->getCreatedAt()->format('c')]
        );
        self::assertStringContainsString('customer created now', $response->getContent());

        $response = $this->cget(
            ['entity' => 'customers'],
            ['filter[createdAt][lt]' => $customer->getCreatedAt()->format('c')]
        );
        self::assertStringNotContainsString('customer created now', $response->getContent());
    }

    public function testGetListFilteredByUpdatedAt()
    {
        $customer = $this->createCustomer('customer created now');
        $this->getEntityManager()->clear();

        $response = $this->cget(
            ['entity' => 'customers'],
            ['filter[updatedAt][lt]' => $customer->getCreatedAt()->format('c')]
        );
        self::assertStringNotContainsString('customer created now', $response->getContent());

        $this->patch(
            ['entity' => 'customers', 'id' => $customer->getId()],
            [
                'data' => [
                    'type'       => 'customers',
                    'id'         => (string)$customer->getId(),
                    'attributes' => [
                        'name' => 'customer created now and updated'
                    ]
                ]
            ]
        );
        $response = $this->cget(
            ['entity' => 'customers'],
            ['filter[updatedAt][gte]' => $customer->getCreatedAt()->format('c')]
        );
        self::assertStringContainsString('customer created now and updated', $response->getContent());
    }

    public function testGet()
    {
        $response = $this->get(
            ['entity' => 'customers', 'id' => '<toString(@customer.1->id)>']
        );
        $this->assertResponseContains('get_customer.yml', $response);
    }

    public function testDelete()
    {
        $customerId = $this->getReference('customer.1')->getId();

        $this->delete(
            ['entity' => 'customers', 'id' => $customerId]
        );

        $deletedCustomer = $this->getEntityManager()
            ->find(Customer::class, $customerId);
        self::assertTrue(null === $deletedCustomer);
    }

    public function testDeleteList()
    {
        $customerId = $this->getReference('customer.1')->getId();

        $this->cdelete(
            ['entity' => 'customers'],
            ['filter[name]' => 'customer.1']
        );

        $deletedCustomer = $this->getEntityManager()
            ->find(Customer::class, $customerId);
        self::assertTrue(null === $deletedCustomer);
    }

    public function testCreate()
    {
        $parentCustomerId = $this->getReference('default_customer')->getId();
        $ownerId = $this->getReference('user')->getId();
        $organizationId = $this->getReference('organization')->getId();
        $internalRatingId = $this->getReference('internal_rating.1 of 5')->getId();
        $groupId = $this->getGroup(LoadGroups::GROUP1)->getId();

        $response = $this->post(
            ['entity' => 'customers'],
            'create_customer.yml'
        );

        $customerId = (int)$this->getResourceId($response);
        $responseContent = $this->updateResponseContent('create_customer.yml', $response);
        $this->assertResponseContains($responseContent, $response);

        /** @var Customer $customer */
        $customer = $this->getEntityManager()
            ->find(Customer::class, $customerId);
        self::assertEquals($organizationId, $customer->getOrganization()->getId());
        self::assertEquals($parentCustomerId, $customer->getParent()->getId());
        self::assertEquals($ownerId, $customer->getOwner()->getId());
        self::assertEquals($internalRatingId, $customer->getInternalRating()->getId());
        self::assertEquals($groupId, $customer->getGroup()->getId());
        self::assertNotEmpty($customer->getCreatedAt());
        self::assertNotEmpty($customer->getUpdatedAt());
        self::assertEquals($customer->getUpdatedAt(), $customer->getCreatedAt());
    }

    public function testTryToCreateWithNewParentCustomerInIncludes()
    {
        $response = $this->post(
            ['entity' => 'customers'],
            'create_customer_with_new_parent_in_includes.yml',
            [],
            false
        );

        $this->assertResponseValidationError(
            [
                'title'  => 'new included entity existence constraint',
                'detail' => 'Creation a new include entity that can lead to a circular dependency is forbidden.',
                'source' => [
                    'pointer' => '/included/0'
                ]
            ],
            $response
        );
    }

    public function testTryToCreateWithNewChildCustomerInIncludes()
    {
        $response = $this->post(
            ['entity' => 'customers'],
            'create_customer_with_new_child_in_includes.yml',
            [],
            false
        );

        $this->assertResponseValidationError(
            [
                'title'  => 'new included entity existence constraint',
                'detail' => 'Creation a new include entity that can lead to a circular dependency is forbidden.',
                'source' => [
                    'pointer' => '/included/0'
                ]
            ],
            $response
        );
    }

    public function testUpdate()
    {
        $customerId = $this->getReference('customer.1')->getId();
        $parentCustomerId = $this->getReference('default_customer')->getId();
        $internalRatingId = $this->getReference('internal_rating.2 of 5')->getId();
        $groupId = $this->getGroup(LoadGroups::GROUP2)->getId();

        $data = [
            'data' => [
                'type'          => 'customers',
                'id'            => (string)$customerId,
                'attributes'    => [
                    'name' => 'Updated Customer'
                ],
                'relationships' => [
                    'parent'          => [
                        'data' => ['type' => 'customers', 'id' => (string)$parentCustomerId]
                    ],
                    'internal_rating' => [
                        'data' => ['type' => 'customerratings', 'id' => $internalRatingId]
                    ],
                    'group'           => [
                        'data' => [
                            'type' => 'customergroups',
                            'id'   => (string)$groupId
                        ]
                    ]
                ]
            ]
        ];
        $this->patch(
            ['entity' => 'customers', 'id' => $customerId],
            $data
        );

        /** @var Customer $customer */
        $customer = $this->getEntityManager()
            ->find(Customer::class, $customerId);
        self::assertEquals('Updated Customer', $customer->getName());
        self::assertEquals($parentCustomerId, $customer->getParent()->getId());
        self::assertEquals($internalRatingId, $customer->getInternalRating()->getId());
        self::assertEquals($groupId, $customer->getGroup()->getId());
    }

    public function testTryToUpdateWithNewParentCustomerInIncludes()
    {
        $response = $this->patch(
            ['entity' => 'customers', 'id' => '<toString(@customer.1->id)>'],
            'update_customer_with_new_parent_in_includes.yml',
            [],
            false
        );

        $this->assertResponseValidationError(
            [
                'title'  => 'new included entity existence constraint',
                'detail' => 'Creation a new include entity that can lead to a circular dependency is forbidden.',
                'source' => [
                    'pointer' => '/included/0'
                ]
            ],
            $response
        );
    }

    public function testTryToUpdateWithNewChildCustomerInIncludes()
    {
        $response = $this->patch(
            ['entity' => 'customers', 'id' => '<toString(@customer.1->id)>'],
            'update_customer_with_new_child_in_includes.yml',
            [],
            false
        );

        $this->assertResponseValidationError(
            [
                'title'  => 'new included entity existence constraint',
                'detail' => 'Creation a new include entity that can lead to a circular dependency is forbidden.',
                'source' => [
                    'pointer' => '/included/0'
                ]
            ],
            $response
        );
    }

    public function testTryToSetCirculiarParent()
    {
        $parentCustomer = $this->createCustomer('parent');
        $customer = $this->createCustomer('customer', null, $parentCustomer);

        $response = $this->patch(
            ['entity' => 'customers', 'id' => $parentCustomer->getId()],
            [
                'data' => [
                    'type'       => 'customers',
                    'id'         => (string)$parentCustomer->getId(),
                    'relationships' => [
                        'parent' => [
                            'data' => [
                                'type' => 'customers',
                                'id' => (string)$customer->getId()
                            ]
                        ]
                    ]
                ]
            ],
            [],
            false
        );

        $this->assertResponseContainsValidationError(
            [
                'title'  => 'circular customer reference constraint',
                'detail' => 'Circular reference detected. '
                    . '\'customer\' cannot be a parent of \'parent\' because it is its child.',
                'source' => ['pointer' => '/data/relationships/parent/data']
            ],
            $response
        );
    }

    public function testTryToSetCirculiarChild()
    {
        $parentCustomer = $this->createCustomer('parent');
        $customer = $this->createCustomer('customer', null, $parentCustomer);

        $response = $this->patch(
            ['entity' => 'customers', 'id' => $customer->getId()],
            [
                'data' => [
                    'type'       => 'customers',
                    'id'         => (string)$customer->getId(),
                    'relationships' => [
                        'children' => [
                            'data' => [
                                [
                                    'type' => 'customers',
                                    'id' => (string)$parentCustomer->getId()
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            [],
            false
        );

        $this->assertResponseContainsValidationError(
            [
                'title'  => 'circular customer reference constraint',
                'detail' => 'Circular reference detected. '
                    . '\'parent\' cannot be a child of \'customer\' because it is its parent.',
                'source' => ['pointer' => '/data/relationships/children/data']
            ],
            $response
        );
    }

    public function testTryToSetCirculiarParentAndChild()
    {
        $parentCustomer = $this->createCustomer('parent');
        $customer = $this->createCustomer('customer');

        $response = $this->patch(
            ['entity' => 'customers', 'id' => $customer->getId()],
            [
                'data' => [
                    'type'       => 'customers',
                    'id'         => (string)$customer->getId(),
                    'relationships' => [
                        'parent' => [
                            'data' => [
                                'type' => 'customers',
                                'id' => (string)$parentCustomer->getId()
                            ]
                        ],
                        'children' => [
                            'data' => [
                                [
                                    'type' => 'customers',
                                    'id' => (string)$parentCustomer->getId()
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            [],
            false
        );

        $this->assertResponseContainsValidationError(
            [
                'title'  => 'circular customer reference constraint',
                'detail' => 'Circular reference detected. '
                    . '\'parent\' cannot be a child of \'customer\' because it is its parent.',
                'source' => ['pointer' => '/data/relationships/children/data']
            ],
            $response
        );
    }

    public function testTryToUpdateCreatedAtAndUpdatedAt()
    {
        /** @var Customer $customer */
        $customer = $this->getReference('customer.1');
        $customerId = $customer->getId();
        $createdAt = $customer->getCreatedAt();
        $updatedAt = $customer->getUpdatedAt();
        $dateTime = new \DateTime('2019-01-01');

        $response = $this->patch(
            ['entity' => 'customers', 'id' => $customerId],
            [
                'data' => [
                    'type'       => 'customers',
                    'id'         => (string)$customerId,
                    'attributes' => [
                        'name'      => 'customer updated',
                        'createdAt' => $dateTime->format('Y-m-d\TH:i:s\Z'),
                        'updatedAt' => $dateTime->format('Y-m-d\TH:i:s\Z')

                    ]
                ]
            ]
        );
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $entity = $this->getEntityManager()->find(Customer::class, $customerId);
        self::assertEquals('customer updated', $entity->getName());
        self::assertEquals($createdAt, $entity->getCreatedAt());
        self::assertTrue($entity->getUpdatedAt() >= $updatedAt && $entity->getUpdatedAt() <= $now);
        $this->assertResponseContains(
            [
                'data' => [
                    'type'       => 'customers',
                    'id'         => (string)$customerId,
                    'attributes' => [
                        'name'      => 'customer updated',
                        'createdAt' => $entity->getCreatedAt()->format('Y-m-d\TH:i:s\Z'),
                        'updatedAt' => $entity->getUpdatedAt()->format('Y-m-d\TH:i:s\Z')

                    ]
                ]
            ],
            $response
        );
    }

    public function testGetRelationshipForGroup()
    {
        $customerId = $this->getReference('customer.1')->getId();

        $response = $this->getRelationship(
            ['entity' => 'customers', 'id' => $customerId, 'association' => 'group']
        );

        $this->assertResponseContains(
            [
                'data' => ['type' => 'customergroups', 'id' => '<toString(@customer.1->group->getId())>']
            ],
            $response
        );
    }

    public function testUpdateRelationshipForGroup()
    {
        $customerId = $this->getReference('customer.1')->getId();
        $groupId = $this->getGroup(LoadGroups::GROUP2)->getId();

        $this->patchRelationship(
            ['entity' => 'customers', 'id' => $customerId, 'association' => 'group'],
            [
                'data' => [
                    'type' => 'customergroups',
                    'id'   => (string)$groupId
                ]
            ]
        );

        /** @var Customer $customer */
        $customer = $this->getEntityManager()
            ->find(Customer::class, $customerId);
        self::assertEquals($groupId, $customer->getGroup()->getId());
    }

    public function testGetSubresourceForInternalRating()
    {
        $customerId = $this->getReference('customer.1')->getId();

        $response = $this->getSubresource(
            ['entity' => 'customers', 'id' => $customerId, 'association' => 'internal_rating']
        );

        $this->assertResponseContains(
            [
                'data' => [
                    'type'       => 'customerratings',
                    'id'         => 'internal_rating.1_of_5',
                    'attributes' => [
                        'name'     => 'internal_rating.1 of 5',
                        'priority' => 1,
                        'default'  => false
                    ]
                ]
            ],
            $response
        );
    }

    public function testGetRelationshipForInternalRating()
    {
        $customerId = $this->getReference('customer.1')->getId();

        $response = $this->getRelationship(
            ['entity' => 'customers', 'id' => $customerId, 'association' => 'internal_rating']
        );

        $this->assertResponseContains(
            [
                'data' => ['type' => 'customerratings', 'id' => 'internal_rating.1_of_5']
            ],
            $response
        );
    }

    public function testUpdateRelationshipForInternalRating()
    {
        $customerId = $this->getReference('customer.1')->getId();

        $this->patchRelationship(
            ['entity' => 'customers', 'id' => $customerId, 'association' => 'internal_rating'],
            [
                'data' => ['type' => 'customerratings', 'id' => 'internal_rating.2_of_5']
            ]
        );

        /** @var Customer $customer */
        $customer = $this->getEntityManager()
            ->find(Customer::class, $customerId);
        self::assertEquals('internal_rating.2 of 5', $customer->getInternalRating()->getName());
    }

    public function testGetSubresourceForOrganization()
    {
        $customerId = $this->getReference('customer.1')->getId();
        $organizationId = $this->getReference('organization')->getId();

        $response = $this->getSubresource(
            ['entity' => 'customers', 'id' => $customerId, 'association' => 'organization']
        );

        $this->assertResponseContains(
            [
                'data' => ['type' => 'organizations', 'id' => (string)$organizationId]
            ],
            $response
        );
    }

    public function testGetRelationshipForOrganization()
    {
        $customerId = $this->getReference('customer.1')->getId();
        $organizationId = $this->getReference('organization')->getId();

        $response = $this->getRelationship(
            ['entity' => 'customers', 'id' => $customerId, 'association' => 'organization']
        );

        $this->assertResponseContains(
            [
                'data' => ['type' => 'organizations', 'id' => (string)$organizationId]
            ],
            $response
        );
    }

    public function testGetSubresourceForOwner()
    {
        $customerId = $this->getReference('customer.1')->getId();
        $ownerId = $this->getReference('user')->getId();

        $response = $this->getSubresource(
            ['entity' => 'customers', 'id' => $customerId, 'association' => 'owner']
        );

        $this->assertResponseContains(
            [
                'data' => ['type' => 'users', 'id' => (string)$ownerId]
            ],
            $response
        );
    }

    public function testGetRelationshipForOwner()
    {
        $customerId = $this->getReference('customer.1')->getId();
        $ownerId = $this->getReference('user')->getId();

        $response = $this->getRelationship(
            ['entity' => 'customers', 'id' => $customerId, 'association' => 'owner']
        );

        $this->assertResponseContains(
            [
                'data' => ['type' => 'users', 'id' => (string)$ownerId]
            ],
            $response
        );
    }

    public function testUpdateRelationshipForOwner()
    {
        $customerId = $this->getReference('customer.1')->getId();
        $ownerId = $this->createUser('another_user')->getId();

        $this->patchRelationship(
            ['entity' => 'customers', 'id' => $customerId, 'association' => 'owner'],
            [
                'data' => ['type' => 'users', 'id' => (string)$ownerId]
            ]
        );

        /** @var Customer $customer */
        $customer = $this->getEntityManager()
            ->find(Customer::class, $customerId);
        self::assertEquals($ownerId, $customer->getOwner()->getId());
    }

    public function testGetSubresourceForParent()
    {
        $response = $this->getSubresource(
            ['entity' => 'customers', 'id' => '@customer.1->id', 'association' => 'parent']
        );

        $this->assertResponseContains('get_parent_sub_resource.yml', $response);
    }

    public function testGetRelationshipForParent()
    {
        $response = $this->getRelationship(
            ['entity' => 'customers', 'id' => '@customer.1->id', 'association' => 'parent']
        );

        $this->assertResponseContains(
            [
                'data' => ['type' => 'customers', 'id' => '<toString(@customer.1->parent->id)>']
            ],
            $response
        );
    }

    public function testUpdateRelationshipForParent()
    {
        $customerId = $this->createCustomer('customer to update parent')->getId();
        $parentId = $this->getReference('customer.1')->getId();
        $this->getEntityManager()->clear();

        $this->patchRelationship(
            ['entity' => 'customers', 'id' => $customerId, 'association' => 'parent'],
            [
                'data' => ['type' => 'customers', 'id' => (string)$parentId]
            ]
        );

        /** @var Customer $customer */
        $customer = $this->getEntityManager()
            ->find(Customer::class, $customerId);
        self::assertEquals($parentId, $customer->getParent()->getId());
    }

    public function testGetSubresourceForChildren()
    {
        $response = $this->getSubresource(
            ['entity' => 'customers', 'id' => '@default_customer->id', 'association' => 'children']
        );

        $this->assertResponseContains('get_children_sub_resource.yml', $response);
    }

    public function testGetRelationshipForChildren()
    {
        $response = $this->getRelationship(
            ['entity' => 'customers', 'id' => '<toString(@default_customer->id)>', 'association' => 'children']
        );

        $this->assertResponseContains(
            [
                'data' => [
                    ['type' => 'customers', 'id' => '<toString(@default_customer->children->first()->id)>']
                ]
            ],
            $response
        );
    }

    public function testAddRelationshipForChildren()
    {
        $customer = $this->createCustomer('new customer');
        $customerId = $customer->getId();
        $child = $this->createCustomer('child customer');
        $childId = $child->getId();
        $customer->addChild($child);
        $additionalChildId = $this->createCustomer('additional customer')->getId();
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        $this->postRelationship(
            ['entity' => 'customers', 'id' => $customerId, 'association' => 'children'],
            [
                'data' => [
                    ['type' => 'customers', 'id' => (string)$additionalChildId]
                ]
            ]
        );

        /** @var Customer $customer */
        $customer = $this->getEntityManager()
            ->find(Customer::class, $customerId);
        self::assertCount(2, $customer->getChildren());
        $this->assertContainsById($additionalChildId, $customer->getChildren());
        $this->assertContainsById($childId, $customer->getChildren());
    }

    public function testUpdateRelationshipForChildren()
    {
        $customer = $this->createCustomer('new customer');
        $customerId = $customer->getId();
        $child = $this->createCustomer('child customer');
        $customer->addChild($child);
        $newChildId = $this->createCustomer('new child customer')->getId();
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        $this->patchRelationship(
            ['entity' => 'customers', 'id' => $customerId, 'association' => 'children'],
            [
                'data' => [
                    ['type' => 'customers', 'id' => (string)$newChildId]
                ]
            ]
        );

        /** @var Customer $customer */
        $customer = $this->getEntityManager()
            ->find(Customer::class, $customerId);
        self::assertCount(1, $customer->getChildren());
        $this->assertContainsById($newChildId, $customer->getChildren());
    }

    public function testDeleteRelationshipForChildren()
    {
        $customer = $this->createCustomer('new customer');
        $customerId = $customer->getId();
        $child1 = $this->createCustomer('child 1');
        $child1Id = $child1->getId();
        $child2 = $this->createCustomer('child 2');
        $child2Id = $child2->getId();
        $customer->addChild($child1);
        $customer->addChild($child2);
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        $this->deleteRelationship(
            ['entity' => 'customers', 'id' => $customerId, 'association' => 'children'],
            [
                'data' => [
                    ['type' => 'customers', 'id' => (string)$child1Id]
                ]
            ]
        );

        /** @var Customer $customer */
        $customer = $this->getEntityManager()
            ->find(Customer::class, $customerId);
        self::assertCount(1, $customer->getChildren());
        $this->assertContainsById($child2Id, $customer->getChildren());
    }

    public function testGetSubresourceForUsers()
    {
        $response = $this->getSubresource(
            ['entity' => 'customers', 'id' => '<toString(@default_customer->id)>', 'association' => 'users']
        );

        $this->assertResponseContains('get_users_sub_resource.yml', $response);
    }

    public function testGetRelationshipForUsers()
    {
        $response = $this->getRelationship(
            ['entity' => 'customers', 'id' => '<toString(@default_customer->id)>', 'association' => 'users']
        );

        $this->assertResponseContains(
            [
                'data' => [
                    ['type' => 'customerusers', 'id' => '<toString(@default_customer_user->id)>']
                ]
            ],
            $response
        );
    }

    public function testAddRelationshipForUsers()
    {
        $customer = $this->createCustomer('new customer');
        $customerId = $customer->getId();
        $user1 = $this->createCustomerUser('user1@oroinc.com', $customer);
        $user1Id = $user1->getId();
        $user2 = $this->createCustomerUser('user2@oroinc.com');
        $user2Id = $user2->getId();
        $this->getEntityManager()->clear();

        $this->postRelationship(
            ['entity' => 'customers', 'id' => $customerId, 'association' => 'users'],
            [
                'data' => [
                    ['type' => 'customerusers', 'id' => (string)$user2Id]
                ]
            ]
        );

        /** @var Customer $customer */
        $customer = $this->getEntityManager()
            ->find(Customer::class, $customerId);
        self::assertCount(2, $customer->getUsers());
        $this->assertContainsById($user1Id, $customer->getUsers());
        $this->assertContainsById($user2Id, $customer->getUsers());
    }

    public function testUpdateRelationshipForUsers()
    {
        $customer = $this->createCustomer('new customer');
        $customerId = $customer->getId();
        $user2 = $this->createCustomerUser('user2@oroinc.com');
        $user2Id = $user2->getId();
        $this->getEntityManager()->clear();

        $this->patchRelationship(
            ['entity' => 'customers', 'id' => $customerId, 'association' => 'users'],
            [
                'data' => [
                    ['type' => 'customerusers', 'id' => (string)$user2Id]
                ]
            ]
        );

        /** @var Customer $customer */
        $customer = $this->getEntityManager()
            ->find(Customer::class, $customerId);
        self::assertCount(1, $customer->getUsers());
        $this->assertContainsById($user2Id, $customer->getUsers());
    }

    public function testDeleteRelationshipForUsers()
    {
        $customer = $this->createCustomer('new customer');
        $customerId = $customer->getId();
        $user1 = $this->createCustomerUser('user1@oroinc.com', $customer);
        $user1Id = $user1->getId();
        $user2 = $this->createCustomerUser('user2@oroinc.com', $customer);
        $user2Id = $user2->getId();
        $this->getEntityManager()->clear();

        $this->deleteRelationship(
            ['entity' => 'customers', 'id' => $customerId, 'association' => 'users'],
            [
                'data' => [
                    ['type' => 'customerusers', 'id' => (string)$user1Id]
                ]
            ]
        );

        /** @var Customer $customer */
        $customer = $this->getEntityManager()
            ->find(Customer::class, $customerId);
        self::assertCount(1, $customer->getUsers());
        $this->assertContainsById($user2Id, $customer->getUsers());
    }
}
