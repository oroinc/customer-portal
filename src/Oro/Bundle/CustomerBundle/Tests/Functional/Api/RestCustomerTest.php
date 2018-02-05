<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Tests\Functional\Api\DataFixtures\LoadCustomerData;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadGroups;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Tests\Functional\DataFixtures\LoadUserData;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class RestCustomerTest extends AbstractRestTest
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->loadFixtures([
            LoadCustomerData::class,
            LoadUserData::class
        ]);
        $this->getReferenceRepository()->setReference('default_customer', $this->getDefaultCustomer());
    }

    /**
     * @group commerce
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetCustomers()
    {
        $response = $this->cget(['entity' => $this->getEntityType(Customer::class)]);
        $this->assertResponseContains(__DIR__.'/responses/get_customers.yml', $response);
        $this->assertContains('createdAt', $response->getContent());
        $this->assertContains('updatedAt', $response->getContent());
    }

    public function testDeleteByFilterCustomer()
    {
        $this->createCustomer('customer to delete');
        $this->getManager()->clear();

        $this->cdelete(
            ['entity' => $this->getEntityType(Customer::class)],
            ['filter' => ['name' => 'customer to delete']]
        );

        $this->assertNull($this->getManager()->getRepository(Customer::class)->findOneByName('customer to delete'));
    }

    public function testGetCustomersFilteredByCreatedAt()
    {
        $customer = $this->createCustomer('customer created now');
        $this->getManager()->clear();

        $response = $this->cget(
            ['entity' => $this->getEntityType(Customer::class)],
            ['filter[createdAt][gte]' => $customer->getCreatedAt()->format('c')]
        );
        $this->assertContains('customer created now', $response->getContent());

        $response = $this->cget(
            ['entity' => $this->getEntityType(Customer::class)],
            ['filter[createdAt][lt]' => $customer->getCreatedAt()->format('c')]
        );
        $this->assertNotContains('customer created now', $response->getContent());

        $this->deleteEntities([$customer]);
    }

    public function testGetCustomersFilteredByUpdatedAt()
    {
        $customer = $this->createCustomer('customer created now');
        $this->getManager()->clear();

        $response = $this->cget(
            ['entity' => $this->getEntityType(Customer::class)],
            ['filter[updatedAt][lt]' => $customer->getCreatedAt()->format('c')]
        );
        $this->assertNotContains('customer created now', $response->getContent());

        $this->patch(
            ['entity' => $this->getEntityType(Customer::class), 'id' => $customer->getId()],
            [
                'data' => [
                    'type' => $this->getEntityType(Customer::class),
                    'id' => (string)$customer->getId(),
                    'attributes' => [
                        'name' => 'customer created now and updated'
                    ]
                ]
            ]
        );
        $response = $this->cget(
            ['entity' => $this->getEntityType(Customer::class)],
            ['filter[updatedAt][gte]' => $customer->getCreatedAt()->format('c')]
        );
        $this->assertContains('customer created now and updated', $response->getContent());

        $this->deleteEntities([$customer]);
    }

    public function testCreateCustomer()
    {
        $parentCustomer = $this->getDefaultCustomer();
        $owner = $parentCustomer->getOwner();
        $organization = $parentCustomer->getOrganization();
        $group = $this->getGroup(LoadGroups::GROUP1);

        $response = $this->post(
            ['entity' => $this->getEntityType(Customer::class)],
            __DIR__.'/requests/create_customer.yml'
        );

        /** @var Customer $customer */
        $customer = $this->getManager()->getRepository(Customer::class)->findOneByName('created customer');
        $this->assertResponseContains(__DIR__.'/responses/create_customer.yml', $response, $customer);
        $this->assertSame($organization->getId(), $customer->getOrganization()->getId());
        $this->assertSame($parentCustomer->getId(), $customer->getParent()->getId());
        $this->assertSame($owner->getId(), $customer->getOwner()->getId());
        $this->assertSame('internal_rating.1 of 5', $customer->getInternalRating()->getName());
        $this->assertSame($group->getId(), $customer->getGroup()->getId());
        $this->assertNotEmpty($customer->getCreatedAt());
        $this->assertNotEmpty($customer->getUpdatedAt());
        $this->assertEquals($customer->getUpdatedAt(), $customer->getCreatedAt());

        $this->deleteEntities([$customer]);
    }

    /**
     * @group commerce
     */
    public function testGetCustomer()
    {
        $response = $this->get([
            'entity' => $this->getEntityType(Customer::class),
            'id' => '<toString(@customer.1->id)>'
        ]);
        $this->assertResponseContains(__DIR__.'/responses/get_customer.yml', $response);
        $this->assertContains('createdAt', $response->getContent());
        $this->assertContains('updatedAt', $response->getContent());
    }

    public function testUpdateCustomer()
    {
        $customer = $this->createCustomer(
            'customer to update',
            $this->getGroup(LoadGroups::GROUP1),
            'internal_rating.1 of 5'
        );
        $parentCustomer = $this->getReference('customer.1');

        $this->patch(
            ['entity' => $this->getEntityType(Customer::class), 'id' => $customer->getId()],
            [
                'data' => [
                    'type' => $this->getEntityType(Customer::class),
                    'id' => (string)$customer->getId(),
                    'attributes' => ['name' => 'customer updated'],
                    'relationships' => [
                        'parent' => [
                            'data' => ['type' => 'customers', 'id' => (string)$parentCustomer->getId()]
                        ],
                        'internal_rating' => [
                            'data' => ['type' => 'customer_rating', 'id' => 'internal_rating.2_of_5']
                        ],
                        'group' => [
                            'data' => [
                                'type' => 'customer_groups',
                                'id' => (string)$this->getGroup(LoadGroups::GROUP2)->getId()
                            ]
                        ]
                    ]
                ]
            ]
        );

        $customer = $this->getManager()->getRepository(Customer::class)->findOneByName('customer updated');
        $this->assertSame($parentCustomer->getId(), $customer->getParent()->getId());
        $this->assertSame('internal_rating.2 of 5', $customer->getInternalRating()->getName());
        $this->assertSame($this->getGroup(LoadGroups::GROUP2)->getId(), $customer->getGroup()->getId());

        $this->deleteEntities([$customer]);
    }

    public function testUpdateCustomerFailsWhenTryingToChangeTimestamps()
    {
        $customer = $this->createCustomer(
            'customer to update',
            $this->getGroup(LoadGroups::GROUP1),
            'internal_rating.1 of 5'
        );

        $response = $this->patch(
            ['entity' => $this->getEntityType(Customer::class), 'id' => $customer->getId()],
            [
                'data' => [
                    'type' => $this->getEntityType(Customer::class),
                    'id' => (string)$customer->getId(),
                    'attributes' => [
                        'name'      => 'customer updated',
                        'createdAt' => new \DateTime(),
                        'updatedAt' => new \DateTime()

                    ]
                ]
            ],
            [],
            false
        );
        $this->assertResponseValidationError(
            [
                'title' => 'extra fields constraint',
                'detail' => 'This form should not contain extra fields: "createdAt", "updatedAt"'
            ],
            $response
        );

        $this->deleteEntities([$customer]);
    }


    public function testGetGroupSubresource()
    {
        /** @var Customer $customer */
        $customer = $this->getReference('customer.1');

        $response = $this->getSubresource([
            'entity' => $this->getEntityType(Customer::class),
            'id' => $customer->getId(),
            'association' => 'group'
        ]);
        $this->assertResponseContains(__DIR__.'/responses/get_group_sub_resourse.yml', $response);
    }

    public function testGetGroupRelationship()
    {
        /** @var Customer $customer */
        $customer = $this->getReference('customer.1');

        $response = $this->getRelationship([
            'entity' => $this->getEntityType(Customer::class),
            'id' => $customer->getId(),
            'association' => 'group'
        ]);
        $this->assertResponseContains(
            [
                'data' => ['type' => 'customer_groups', 'id' => '<toString(@customer.1->getGroup()->getId())>']
            ],
            $response
        );
    }

    public function testUpdateGroupRelationship()
    {
        $customer = $this->createCustomer('customer to update group', $this->getGroup(LoadGroups::GROUP1));

        $this->patchRelationship(
            [
                'entity' => $this->getEntityType(Customer::class),
                'id' => $customer->getId(),
                'association' => 'group'
            ],
            [
                'data' => ['type' => 'customer_groups', 'id' => (string)$this->getGroup(LoadGroups::GROUP2)->getId()]
            ]
        );

        $customer = $this->getManager()->getRepository(Customer::class)->findOneByName('customer to update group');
        $this->assertSame($this->getGroup(LoadGroups::GROUP2)->getId(), $customer->getGroup()->getId());

        $this->deleteEntities([$customer]);
    }

    public function testGetInternalRatingSubresource()
    {
        /** @var Customer $customer */
        $customer = $this->getReference('customer.1');

        $response = $this->getSubresource([
            'entity' => $this->getEntityType(Customer::class),
            'id' => $customer->getId(),
            'association' => 'internal_rating'
        ]);
        $this->assertResponseContains(
            [
                'data' => [
                    'type' => 'customer_rating',
                    'id' => 'internal_rating.1_of_5',
                    'attributes' => [
                        'name' => 'internal_rating.1 of 5',
                        'priority' => 1,
                        'default' => false
                    ]
                ]
            ],
            $response
        );
    }

    public function testGetRatingRelationship()
    {
        /** @var Customer $customer */
        $customer = $this->getReference('customer.1');

        $response = $this->getRelationship([
            'entity' => $this->getEntityType(Customer::class),
            'id' => $customer->getId(),
            'association' => 'internal_rating'
        ]);
        $this->assertResponseContains(
            [
                'data' => ['type' => 'customer_rating', 'id' => 'internal_rating.1_of_5']
            ],
            $response
        );
    }

    public function testUpdateRatingRelationship()
    {
        $customer = $this->createCustomer('customer to update rating');

        $this->patchRelationship(
            [
                'entity' => $this->getEntityType(Customer::class),
                'id' => $customer->getId(),
                'association' => 'internal_rating'
            ],
            [
                'data' => ['type' => 'customer_rating', 'id' => 'internal_rating.2_of_5']
            ]
        );

        $customer = $this->getManager()->getRepository(Customer::class)->findOneByName('customer to update rating');
        $this->assertSame('internal_rating.2 of 5', $customer->getInternalRating()->getName());

        $this->deleteEntities([$customer]);
    }

    public function testGetOrganizationSubresource()
    {
        /** @var Customer $customer */
        $customer = $this->getReference('customer.1');
        $organization = $customer->getOrganization();

        $response = $this->getSubresource([
            'entity' => $this->getEntityType(Customer::class),
            'id' => $customer->getId(),
            'association' => 'organization'
        ]);
        $this->assertResponseContains(
            [
                'data' => ['type' => 'organizations', 'id' => (string)$organization->getId()]
            ],
            $response
        );
    }

    public function testGetOrganizationRelationship()
    {
        /** @var Customer $customer */
        $customer = $this->getReference('customer.1');
        $organization = $customer->getOrganization();

        $response = $this->getRelationship([
            'entity' => $this->getEntityType(Customer::class),
            'id' => $customer->getId(),
            'association' => 'organization',
        ]);
        $this->assertResponseContains(
            [
                'data' => ['type' => 'organizations', 'id' => (string)$organization->getId()]
            ],
            $response
        );
    }

    public function testUpdateOrganizationRelationship()
    {
        $customer = $this->createCustomer('customer to update organization');
        $organization = new Organization();
        $organization->setName('org name')
            ->setEnabled(true);
        $this->getManager()->persist($organization);
        $this->getManager()->flush();

        $this->patchRelationship(
            [
                'entity' => $this->getEntityType(Customer::class),
                'id' => $customer->getId(),
                'association' => 'organization'
            ],
            [
                'data' => ['type' => 'organizations', 'id' => (string)$organization->getId()]
            ]
        );

        $customer = $this->getManager()->getRepository(Customer::class)
            ->findOneByName('customer to update organization');
        $this->assertSame($organization->getId(), $customer->getOrganization()->getId());

        $this->deleteEntities([$customer]);
    }

    public function testGetOwnerSubresource()
    {
        /** @var Customer $customer */
        $customer = $this->getReference('customer.1');
        $owner = $customer->getOwner();

        $response = $this->getSubresource([
            'entity' => $this->getEntityType(Customer::class),
            'id' => $customer->getId(),
            'association' => 'owner'
        ]);
        $this->assertResponseContains(
            [
                'data' => ['type' => 'users', 'id' => (string)$owner->getId()]
            ],
            $response
        );
    }

    public function testGetOwnerRelationship()
    {
        /** @var Customer $customer */
        $customer = $this->getReference('customer.1');
        $owner = $customer->getOwner();

        $response = $this->getRelationship([
            'entity' => $this->getEntityType(Customer::class),
            'id' => $customer->getId(),
            'association' => 'owner'
        ]);
        $this->assertResponseContains(
            [
                'data' => ['type' => 'users', 'id' => (string)$owner->getId()]
            ],
            $response
        );
    }

    public function testUpdateOwnerRelationship()
    {
        $customer = $this->createCustomer('customer to update owner');
        /** @var User $user */
        $user = $this->getReference(LoadUserData::SIMPLE_USER);

        $this->patchRelationship(
            [
                'entity' => $this->getEntityType(Customer::class),
                'id' => $customer->getId(),
                'association' => 'owner'
            ],
            [
                'data' => ['type' => 'users', 'id' => (string)$user->getId()]
            ]
        );

        $customer = $this->getManager()->getRepository(Customer::class)
            ->findOneByName('customer to update owner');
        $this->assertSame($user->getId(), $customer->getOwner()->getId());

        $this->deleteEntities([$customer]);
    }

    /**
     * @group commerce
     */
    public function testGetParentSubresource()
    {
        $response = $this->getSubresource([
            'entity' => $this->getEntityType(Customer::class),
            'id' => '@customer.1->id',
            'association' => 'parent'
        ]);
        $this->assertResponseContains(__DIR__.'/responses/get_parent_sub_resource.yml', $response);
    }

    public function testGetParentRelationship()
    {
        $response = $this->getRelationship([
            'entity' => $this->getEntityType(Customer::class),
            'id' => '@customer.1->id',
            'association' => 'parent'
        ]);
        $this->assertResponseContains(
            [
                'data' => ['type' => 'customers', 'id' => '<toString(@customer.1->getParent()->id)>']
            ],
            $response
        );
    }

    public function testUpdateParentRelationship()
    {
        $customer = $this->createCustomer('customer to update parent');
        $parent = $this->getReference('customer.1');

        $this->patchRelationship(
            [
                'entity' => $this->getEntityType(Customer::class),
                'id' => $customer->getId(),
                'association' => 'parent'
            ],
            [
                'data' => ['type' => 'customers', 'id' => (string)$parent->getId()]
            ]
        );

        $customer = $this->getManager()->getRepository(Customer::class)
            ->findOneByName('customer to update parent');
        $this->assertSame($parent->getId(), $customer->getParent()->getId());

        $this->deleteEntities([$customer]);
    }

    /**
     * @group commerce
     */
    public function testGetChildrenSubresource()
    {
        $response = $this->getSubresource([
            'entity' => $this->getEntityType(Customer::class),
            'id' => '@default_customer->id',
            'association' => 'children'
        ]);
        $this->assertResponseContains(__DIR__.'/responses/get_children_sub_resource.yml', $response);
    }

    public function testGetChildrenRelationship()
    {
        $response = $this->getRelationship([
            'entity' => $this->getEntityType(Customer::class),
            'id' => '<toString(@default_customer->id)>',
            'association' => 'children'
        ]);
        $this->assertResponseContains(
            [
                'data' => [
                    ['type' => 'customers', 'id' => '<toString(@default_customer->getChildren()->first()->id)>']
                ]
            ],
            $response
        );
    }

    public function testAddChildrenRelationship()
    {
        $customer = $this->createCustomer('new customer');
        $child = $this->createCustomer('child customer');
        $customer->addChild($child);
        $this->getManager()->flush();

        $additionalChild = $this->createCustomer('additional customer');

        $this->postRelationship(
            [
                'entity' => $this->getEntityType(Customer::class),
                'id' => $customer->getId(),
                'association' => 'children'
            ],
            [
                'data' => [
                    ['type' => 'customers', 'id' => (string)$additionalChild->getId()]
                ]
            ]
        );

        $customer = $this->getManager()->getRepository(Customer::class)->findOneByName('new customer');
        $this->assertCount(2, $customer->getChildren());
        $this->assertContainsById($additionalChild, $customer->getChildren());
        $this->assertContainsById($child, $customer->getChildren());
        $this->deleteEntities([$additionalChild, $child, $customer]);
    }

    public function testPatchChildrenRelationship()
    {
        $customer = $this->createCustomer('new customer');
        $child = $this->createCustomer('child customer');
        $customer->addChild($child);
        $this->getManager()->flush();

        $newChild = $this->createCustomer('new child customer');

        $this->patchRelationship(
            [
                'entity' => $this->getEntityType(Customer::class),
                'id' => $customer->getId(),
                'association' => 'children'
            ],
            [
                'data' => [
                    ['type' => 'customers', 'id' => (string)$newChild->getId()]
                ]
            ]
        );

        $customer = $this->getManager()->getRepository(Customer::class)->findOneByName('new customer');
        $this->assertCount(1, $customer->getChildren());
        $this->assertContainsById($newChild, $customer->getChildren());

        $this->deleteEntities([$child, $newChild, $customer]);
    }

    public function testDeleteChildrenRelationship()
    {
        $customer = $this->createCustomer('new customer');
        $child1 = $this->createCustomer('child 1');
        $child2 = $this->createCustomer('child 2');
        $customer->addChild($child1);
        $customer->addChild($child2);

        $this->getManager()->flush();

        $this->deleteRelationship(
            [
                'entity' => $this->getEntityType(Customer::class),
                'id' => $customer->getId(),
                'association' => 'children'
            ],
            [
                'data' => [
                    ['type' => 'customers', 'id' => (string)$child1->getId()]
                ]
            ]
        );

        $customer = $this->getManager()->getRepository(Customer::class)->findOneByName('new customer');
        $this->assertCount(1, $customer->getChildren());
        $this->assertContainsById($child2, $customer->getChildren());

        $this->deleteEntities([$customer, $child1, $child2]);
    }

    public function testGetUsersSubresource()
    {
        $response = $this->getSubresource([
            'entity' => $this->getEntityType(Customer::class),
            'id' => '<toString(@default_customer->id)>',
            'association' => 'users'
        ]);
        $this->assertResponseContains(__DIR__.'/responses/get_users_sub_resource.yml', $response);
    }

    public function testGetUsersRelationship()
    {
        $response = $this->getRelationship([
            'entity' => $this->getEntityType(Customer::class),
            'id' => '<toString(@default_customer->id)>',
            'association' => 'users'
        ]);
        $this->assertResponseContains(
            [
                'data' => [
                    ['type' => 'customer_users', 'id' => '<toString(@default_customer->getUsers()->first()->id)>']
                ]
            ],
            $response
        );
    }

    public function testAddUsersRelationship()
    {
        $customer = $this->createCustomer('new customer');
        $user1 = $this->createCustomerUser('user1@oroinc.com', $customer);
        $user2 = $this->createCustomerUser('user2@oroinc.com');

        $this->postRelationship(
            [
                'entity' => $this->getEntityType(Customer::class),
                'id' => $customer->getId(),
                'association' => 'users'
            ],
            [
                'data' => [
                    ['type' => 'customer_users', 'id' => (string)$user2->getId()]
                ]
            ]
        );

        $customer = $this->getManager()->getRepository(Customer::class)->findOneByName('new customer');
        $this->assertCount(2, $customer->getUsers());
        $this->assertContainsById($user1, $customer->getUsers());
        $this->assertContainsById($user2, $customer->getUsers());

        $this->deleteEntities([$user1, $user2, $customer]);
    }

    public function testPatchUsersRelationship()
    {
        $customer = $this->createCustomer('new customer');
        $user1 = $this->createCustomerUser('user1@oroinc.com', $customer);
        $user2 = $this->createCustomerUser('user2@oroinc.com');

        $this->patchRelationship(
            [
                'entity' => $this->getEntityType(Customer::class),
                'id' => $customer->getId(),
                'association' => 'users'
            ],
            [
                'data' => [
                    ['type' => 'customer_users', 'id' => (string)$user2->getId()]
                ]
            ]
        );

        $customer = $this->getManager()->getRepository(Customer::class)->findOneByName('new customer');
        $this->assertCount(1, $customer->getUsers());
        $this->assertContainsById($user2, $customer->getUsers());

        $this->deleteEntities([$user1, $user2, $customer]);
    }

    public function testDeleteUsersRelationship()
    {
        $customer = $this->createCustomer('new customer');
        $user1 = $this->createCustomerUser('user1@oroinc.com', $customer);
        $user2 = $this->createCustomerUser('user2@oroinc.com', $customer);

        $this->deleteRelationship(
            [
                'entity' => $this->getEntityType(Customer::class),
                'id' => $customer->getId(),
                'association' => 'users'
            ],
            [
                'data' => [
                    ['type' => 'customer_users', 'id' => (string)$user1->getId()]
                ]
            ]
        );

        $customer = $this->getManager()->getRepository(Customer::class)->findOneByName('new customer');
        $this->assertCount(1, $customer->getUsers());
        $this->assertContainsById($user2, $customer->getUsers());

        $this->deleteEntities([$user1, $user2, $customer]);
    }

    /**
     * @param int $parentId
     * @param int $ownerId
     * @param int $organizationId
     * @return array
     */
    protected function getRelationships($parentId, $ownerId, $organizationId)
    {
        return [
            'parent' => [
                'data' => [
                    'type' => 'customers',
                    'id' => (string)$parentId,
                ],
            ],
            'children' => ['data' => [],],
            'users' => ['data' => [],],
            'owner' => [
                'data' => [
                    'type' => 'users',
                    'id' => (string)$ownerId,
                ],
            ],
            'organization' => [
                'data' => [
                    'type' => 'organizations',
                    'id' => (string)$organizationId,
                ],
            ],
            'salesRepresentatives' => [
                'data' => [
                    [
                        'type' => 'users',
                        'id' => (string)$ownerId,
                    ],
                ],
            ],
            'internal_rating' => [
                'data' =>
                    [
                        'type' => 'customer_rating',
                        'id' => 'internal_rating.1_of_5',
                    ],
            ],
            'group' => [
                'data' => [
                    'type' => 'customer_groups',
                    'id' => (string)$this->getGroup(LoadGroups::GROUP1)->getId(),
                ],
            ],
        ];
    }
}
