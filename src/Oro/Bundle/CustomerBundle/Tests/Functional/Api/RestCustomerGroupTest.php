<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api;

use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\UserBundle\Tests\Functional\DataFixtures\LoadUserData;

/**
 * @dbIsolationPerTest
 */
class RestCustomerGroupTest extends AbstractRestTest
{
    protected function setUp()
    {
        parent::setUp();
        $this->loadFixtures([LoadUserData::class]);
    }

    public function testGetCustomerGroups()
    {
        $group = $this->createCustomerGroup('test group');
        $customer1 = $this->createCustomer('customer1', $group);
        $customer2 = $this->createCustomer('customer2', $group);

        $response = $this->cget(
            ['entity' => $this->getEntityType(CustomerGroup::class)],
            ['filter[name]' => 'test group']
        );

        $this->assertResponseContains(
            [
                'data' => [
                    [
                        'type' => 'customer_groups',
                        'id' => (string)$group->getId(),
                        'attributes' => [
                            'name' => 'test group'
                        ],
                        'relationships' => [
                            'customers' => [
                                'data' => [
                                    ['type' => 'customers', 'id' => (string)$customer1->getId()],
                                    ['type' => 'customers', 'id' => (string)$customer2->getId()]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            $response
        );

        $this->deleteEntities([$group, $customer1, $customer2]);
    }

    public function testDeleteByFilterCustomerGroup()
    {
        $this->createCustomerGroup('group to delete');
        $this->getManager()->clear();

        $this->cdelete(
            ['entity' => $this->getEntityType(CustomerGroup::class)],
            ['filter[name]' => 'group to delete']
        );

        $this->assertNull($this->getManager()->getRepository(CustomerGroup::class)->findOneByName('group to delete'));
    }

    public function testCreateCustomerGroup()
    {
        $customer1 = $this->createCustomer('customer1');
        $customer2 = $this->createCustomer('customer2');

        $this->post(
            ['entity' => $this->getEntityType(CustomerGroup::class)],
            [
                'data' => [
                    'type' => 'customer_groups',
                    'attributes' => [
                        'name' => 'new group'
                    ],
                    'relationships' => [
                        'customers' => [
                            'data' => [
                                ['type' => 'customers', 'id' => (string)$customer1->getId()],
                                ['type' => 'customers', 'id' => (string)$customer2->getId()]
                            ]
                        ]
                    ]
                ]
            ]
        );

        $group = $this->getManager()->getRepository(CustomerGroup::class)->findOneByName('new group');

        $this->assertCount(2, $group->getCustomers());
        $this->assertContainsById($customer1, $group->getCustomers());
        $this->assertContainsById($customer2, $group->getCustomers());

        $this->deleteEntities([$customer1, $customer2, $group]);
    }

    public function testGetCustomerGroup()
    {
        $group = $this->createCustomerGroup('test group');
        $customer1 = $this->createCustomer('customer1', $group);
        $customer2 = $this->createCustomer('customer2', $group);

        $response = $this->get([
            'entity' => $this->getEntityType(CustomerGroup::class),
            'id' => (string)$group->getId(),
        ]);

        $this->assertResponseContains(
            [
                'data' => [
                    'type' => 'customer_groups',
                    'id' => (string)$group->getId(),
                    'attributes' => [
                        'name' => 'test group'
                    ],
                    'relationships' => [
                        'customers' => [
                            'data' => [
                                ['type' => 'customers', 'id' => (string)$customer1->getId()],
                                ['type' => 'customers', 'id' => (string)$customer2->getId()]
                            ]
                        ]
                    ]
                ]
            ],
            $response
        );

        $this->deleteEntities([$group, $customer1, $customer2]);
    }

    public function testUpdateCustomerGroup()
    {
        $group = $this->createCustomerGroup('group to update');
        $customer1 = $this->createCustomer('customer1', $group);
        $customer2 = $this->createCustomer('customer2');

        $this->patch(
            [
                'entity' => $this->getEntityType(CustomerGroup::class),
                'id' => $group->getId()
            ],
            [
                'data' => [
                    'type' => 'customer_groups',
                    'id' => (string)$group->getId(),
                    'attributes' => [
                        'name' => 'updated group'
                    ],
                    'relationships' => [
                        'customers' => [
                            'data' => [
                                ['type' => 'customers', 'id' => (string)$customer2->getId()]
                            ]
                        ]
                    ]
                ]
            ]
        );

        // @todo: uncomment after fix BB-13631
        //$group = $this->getGroup('updated group');
        //$this->assertCount(1, $group->getCustomers());
        //$this->assertContainsById($customer2, $group->getCustomers());

        $this->deleteEntities([$customer1, $customer2, $group]);
    }

    public function testGetCustomersSubresource()
    {
        $group = $this->createCustomerGroup('new group');
        $customer = $this->createCustomer('customer', $group);

        $response = $this->getSubresource([
            'entity' => $this->getEntityType(CustomerGroup::class),
            'id' => $group->getId(),
            'association' => 'customers'
        ]);
        $this->assertResponseContains(
            [
                'data' => [
                    ['type' => 'customers', 'id' => (string)$customer->getId()]
                ]
            ],
            $response
        );

        $this->deleteEntities([$group, $customer]);
    }

    public function testGetCustomersRelationship()
    {
        $group = $this->createCustomerGroup('test group');
        $customer1 = $this->createCustomer('customer1', $group);
        $customer2 = $this->createCustomer('customer2', $group);

        $response = $this->getRelationship([
            'entity' => $this->getEntityType(CustomerGroup::class),
            'id' => $group->getId(),
            'association' => 'customers'
        ]);
        $this->assertResponseContains(
            [
                'data' => [
                    ['type' => 'customers', 'id' => (string)$customer1->getId()],
                    ['type' => 'customers', 'id' => (string)$customer2->getId()]
                ]
            ],
            $response
        );

        $this->deleteEntities([$customer1, $customer2, $group]);
    }

    public function testAddCustomersRelationship()
    {
        $group = $this->createCustomerGroup('test group');
        $customer1 = $this->createCustomer('customer1', $group);
        $customer2 = $this->createCustomer('customer2');

        $this->postRelationship(
            [
                'entity' => $this->getEntityType(CustomerGroup::class),
                'id' => $group->getId(),
                'association' => 'customers'
            ],
            [
                'data' => [
                    ['type' => 'customers', 'id' => (string)$customer2->getId()]
                ]
            ]
        );

        $group = $this->getGroup('test group');
        $this->assertCount(2, $group->getCustomers());
        $this->assertContainsById($customer1, $group->getCustomers());
        $this->assertContainsById($customer2, $group->getCustomers());

        $this->deleteEntities([$customer1, $customer2, $group]);
    }

    public function testPatchCustomersRelationship()
    {
        $group = $this->createCustomerGroup('test group');
        $customer1 = $this->createCustomer('customer1', $group);
        $customer2 = $this->createCustomer('customer2');

        $this->patchRelationship(
            [
                'entity' => $this->getEntityType(CustomerGroup::class),
                'id' => (string)$group->getId(),
                'association' => 'customers'
            ],
            [
                'data' => [
                    ['type' => 'customers', 'id' => (string)$customer2->getId()]
                ]
            ]
        );

        // @todo: uncomment after fix BB-13631
        //$group = $this->getGroup('test group');
        //$this->assertCount(1, $group->getCustomers());
        //$this->assertContainsById($customer2, $group->getCustomers());

        $this->deleteEntities([$customer1, $customer2, $group]);
    }

    public function testDeleteCustomersRelationship()
    {
        $group = $this->createCustomerGroup('test group');
        $customer1 = $this->createCustomer('customer1', $group);
        $customer2 = $this->createCustomer('customer2', $group);

        $this->deleteRelationship(
            [
                'entity' => $this->getEntityType(CustomerGroup::class),
                'id' => (string)$group->getId(),
                'association' => 'customers'
            ],
            [
                'data' => [
                    ['type' => 'customers', 'id' => (string)$customer1->getId()]
                ]
            ]
        );

        // @todo: uncomment after fix BB-13631
        //$group = $this->getGroup('test group');
        //$this->assertCount(1, $group->getCustomers());
        //$this->assertContainsById($customer2, $group->getCustomers());

        $this->deleteEntities([$customer1, $customer2, $group]);
    }
}
