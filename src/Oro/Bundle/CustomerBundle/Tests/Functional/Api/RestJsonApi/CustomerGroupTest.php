<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api\RestJsonApi;

use Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApiTestCase;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;

/**
 * @dbIsolationPerTest
 */
class CustomerGroupTest extends RestJsonApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures([
            '@OroCustomerBundle/Tests/Functional/Api/DataFixtures/customer_groups.yml'
        ]);
    }

    public function testGetList()
    {
        $response = $this->cget(
            ['entity' => 'customergroups'],
            ['filter[name]' => 'Group 2']
        );

        $this->assertResponseContains('cget_customer_group.yml', $response);
    }

    public function testGet()
    {
        $response = $this->get(
            ['entity' => 'customergroups', 'id' => '<toString(@customer_group1->id)>']
        );

        $this->assertResponseContains('get_customer_group.yml', $response);
    }

    public function testDelete()
    {
        $groupId = $this->getReference('customer_group1')->getId();

        $this->delete(
            ['entity' => 'customergroups', 'id' => (string)$groupId]
        );

        $deletedGroup = $this->getEntityManager()
            ->find(CustomerGroup::class, $groupId);
        self::assertTrue(null === $deletedGroup);
    }

    public function testDeleteList()
    {
        $groupId = $this->getReference('customer_group1')->getId();

        $this->cdelete(
            ['entity' => 'customergroups'],
            ['filter[name]' => 'Group 1']
        );

        $deletedGroup = $this->getEntityManager()
            ->find(CustomerGroup::class, $groupId);
        self::assertTrue(null === $deletedGroup);
    }

    public function testCreate()
    {
        $organizationId = $this->getReference('organization')->getId();
        $userId = $this->getReference('user')->getId();

        $data = [
            'data' => [
                'type'       => 'customergroups',
                'attributes' => [
                    'name' => 'New Group'
                ]
            ]
        ];
        $response = $this->post(
            ['entity' => 'customergroups'],
            $data
        );

        $expectedData = $data;
        $expectedData['data']['relationships']['organization']['data'] = [
            'type' => 'organizations',
            'id'   => (string)$organizationId
        ];
        $expectedData['data']['relationships']['owner']['data'] = [
            'type' => 'users',
            'id'   => (string)$userId
        ];
        $this->assertResponseContains($expectedData, $response);

        $customerGroup = $this->getEntityManager()
            ->find(CustomerGroup::class, $this->getResourceId($response));
        self::assertNotNull($customerGroup);
        self::assertEquals('New Group', $customerGroup->getName());
        self::assertEquals($organizationId, $customerGroup->getOrganization()->getId());
        self::assertEquals($userId, $customerGroup->getOwner()->getId());
    }

    public function testUpdate()
    {
        $groupId = $this->getReference('customer_group1')->getId();

        $data = [
            'data' => [
                'type'       => 'customergroups',
                'id'         => (string)$groupId,
                'attributes' => [
                    'name' => 'Updated Group'
                ]
            ]
        ];
        $response = $this->patch(
            ['entity' => 'customergroups', 'id' => (string)$groupId],
            $data
        );

        $customerGroup = $this->getEntityManager()
            ->find(CustomerGroup::class, $this->getResourceId($response));
        self::assertNotNull($customerGroup);
        self::assertEquals('Updated Group', $customerGroup->getName());
    }

    public function testTryToCreateGroupWithoutName()
    {
        $data = [
            'data' => [
                'type' => 'customergroups'
            ]
        ];
        $response = $this->post(
            ['entity' => 'customergroups'],
            $data,
            [],
            false
        );

        $this->assertResponseValidationError(
            [
                'title'  => 'not blank constraint',
                'detail' => 'This value should not be blank.',
                'source' => ['pointer' => '/data/attributes/name']
            ],
            $response
        );
    }

    public function testTryToSetGroupNameToNull()
    {
        $groupId = $this->getReference('customer_group1')->getId();

        $data = [
            'data' => [
                'type'       => 'customergroups',
                'id'         => (string)$groupId,
                'attributes' => [
                    'name' => null
                ]
            ]
        ];
        $response = $this->patch(
            ['entity' => 'customergroups', 'id' => (string)$groupId],
            $data,
            [],
            false
        );

        $this->assertResponseValidationError(
            [
                'title'  => 'not blank constraint',
                'detail' => 'This value should not be blank.',
                'source' => ['pointer' => '/data/attributes/name']
            ],
            $response
        );
    }
}
