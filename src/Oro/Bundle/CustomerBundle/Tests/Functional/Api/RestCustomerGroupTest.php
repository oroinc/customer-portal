<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api;

use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\UserBundle\Tests\Functional\DataFixtures\LoadUserData;

/**
 * @dbIsolationPerTest
 */
class RestCustomerGroupTest extends AbstractRestTest
{
    /**
     * @var CustomerGroup
     */
    private $customerGroup;

    protected function setUp()
    {
        parent::setUp();
        $this->loadFixtures([LoadUserData::class]);
    }

    protected function tearDown()
    {
        if ($this->customerGroup) {
            $this->deleteEntities([$this->customerGroup]);
        }
    }

    public function testGetCustomerGroups()
    {
        $this->customerGroup = $this->createCustomerGroup('test group');

        $response = $this->cget(
            ['entity' => $this->getEntityType(CustomerGroup::class)],
            ['filter[name]' => 'test group']
        );

        $this->assertResponseContains(
            [
                'data' => [
                    [
                        'type' => 'customer_groups',
                        'id' => (string)$this->customerGroup->getId(),
                        'attributes' => [
                            'name' => 'test group'
                        ],
                    ]
                ]
            ],
            $response
        );
    }

    public function testDeleteByFilterCustomerGroup()
    {
        $this->createCustomerGroup('group to delete');
        $this->getManager()->clear();

        $this->cdelete(
            ['entity' => $this->getEntityType(CustomerGroup::class)],
            ['filter' => ['name' => 'group to delete']]
        );

        $this->assertNull($this->getManager()->getRepository(CustomerGroup::class)->findOneByName('group to delete'));
    }

    public function testCreateCustomerGroup()
    {
        $this->post(
            ['entity' => $this->getEntityType(CustomerGroup::class)],
            [
                'data' => [
                    'type' => 'customer_groups',
                    'attributes' => [
                        'name' => 'new group'
                    ]
                ]
            ]
        );

        $this->customerGroup = $this->getManager()->getRepository(CustomerGroup::class)->findOneByName('new group');

        $this->assertNotNull($this->customerGroup);
    }

    public function testGetCustomerGroup()
    {
        $this->customerGroup = $this->createCustomerGroup('test group');

        $response = $this->get([
            'entity' => $this->getEntityType(CustomerGroup::class),
            'id' => (string)$this->customerGroup->getId(),
        ]);

        $this->assertResponseContains(
            [
                'data' => [
                    'type' => 'customer_groups',
                    'id' => (string)$this->customerGroup->getId(),
                    'attributes' => [
                        'name' => 'test group'
                    ]
                ]
            ],
            $response
        );
    }

    public function testUpdateCustomerGroup()
    {
        $this->customerGroup = $this->createCustomerGroup('group to update');

        $this->patch(
            [
                'entity' => $this->getEntityType(CustomerGroup::class),
                'id' => $this->customerGroup->getId()
            ],
            [
                'data' => [
                    'type' => 'customer_groups',
                    'id' => (string)$this->customerGroup->getId(),
                    'attributes' => [
                        'name' => 'updated group'
                    ]
                ]
            ]
        );

        $this->customerGroup = $this->getGroup('updated group');
        $this->assertNotNull($this->customerGroup);
    }
}
