<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api\Frontend\RestJsonApi;

use Oro\Bundle\CustomerBundle\Tests\Functional\Api\Frontend\DataFixtures\LoadAdminCustomerUserData;
use Oro\Bundle\FrontendBundle\Tests\Functional\Api\FrontendRestJsonApiTestCase;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class CustomerTest extends FrontendRestJsonApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures([
            LoadAdminCustomerUserData::class,
            '@OroCustomerBundle/Tests/Functional/Api/Frontend/DataFixtures/customer.yml'
        ]);
    }

    public function testGetList()
    {
        $response = $this->cget(['entity' => 'customers']);

        $this->assertResponseContains('cget_customer.yml', $response);
    }

    public function testGetListFilteredByMineId()
    {
        $response = $this->cget(
            ['entity' => 'customers'],
            ['filter' => ['id' => 'mine']]
        );

        $this->assertResponseContains('cget_customer_mine.yml', $response);
    }

    public function testGet()
    {
        $response = $this->get(
            ['entity' => 'customers', 'id' => '<toString(@customer3->id)>']
        );

        $this->assertResponseContains('get_customer.yml', $response);
    }

    public function testGetByMineId()
    {
        $response = $this->get(
            ['entity' => 'customers', 'id' => 'mine']
        );

        $this->assertResponseContains('get_customer_mine.yml', $response);
    }

    public function testTryToCreate()
    {
        $response = $this->post(
            ['entity' => 'customers'],
            [],
            [],
            false
        );

        self::assertMethodNotAllowedResponse($response, 'OPTIONS, GET');
    }

    public function testTryToUpdate()
    {
        $response = $this->patch(
            ['entity' => 'customers', 'id' => '<toString(@customer1->id)>'],
            [],
            [],
            false
        );

        self::assertMethodNotAllowedResponse($response, 'OPTIONS, GET');
    }

    public function testTryToDelete()
    {
        $response = $this->delete(
            ['entity' => 'customers', 'id' => '<toString(@customer1->id)>'],
            [],
            [],
            false
        );

        self::assertMethodNotAllowedResponse($response, 'OPTIONS, GET');
    }

    public function testTryToDeleteList()
    {
        $response = $this->cdelete(
            ['entity' => 'customers'],
            ['filter' => ['id' => '<toString(@customer1->id)>']],
            [],
            false
        );

        self::assertMethodNotAllowedResponse($response, 'OPTIONS, GET');
    }

    public function testGetUsersSubresourceByMineId()
    {
        $response = $this->getSubresource(
            ['entity' => 'customers', 'id' => 'mine', 'association' => 'users']
        );

        $this->assertResponseContains(
            [
                'data' => [
                    [
                        'type'       => 'customerusers',
                        'id'         => '<toString(@customer_user->id)>',
                        'attributes' => [
                            'email' => 'frontend_admin_api@example.com'
                        ]
                    ]
                ]
            ],
            $response
        );
    }

    public function testGetUsersRelationshipByMineId()
    {
        $response = $this->getRelationship(
            ['entity' => 'customers', 'id' => 'mine', 'association' => 'users']
        );

        $this->assertResponseContains(
            [
                'data' => [
                    [
                        'type' => 'customerusers',
                        'id'   => '<toString(@customer_user->id)>'
                    ]
                ]
            ],
            $response
        );
    }

    public function testGetSubresourceForUsers()
    {
        $response = $this->getSubresource(
            ['entity' => 'customers', 'id' => '<toString(@customer3->id)>', 'association' => 'users']
        );

        $this->assertResponseContains(
            [
                'data' => [
                    [
                        'type'       => 'customerusers',
                        'id'         => '<toString(@customer_user3->id)>',
                        'attributes' => [
                            'email' => 'user3@example.com'
                        ]
                    ]
                ]
            ],
            $response
        );
    }

    public function testGetRelationshipForUsers()
    {
        $response = $this->getRelationship(
            ['entity' => 'customers', 'id' => '<toString(@customer3->id)>', 'association' => 'users']
        );

        $this->assertResponseContains(
            [
                'data' => [
                    ['type' => 'customerusers', 'id' => '<toString(@customer_user3->id)>']
                ]
            ],
            $response
        );
    }

    public function testTryToAddRelationshipForUsers()
    {
        $response = $this->postRelationship(
            ['entity' => 'customers', 'id' => '<toString(@customer3->id)>', 'association' => 'users'],
            [],
            [],
            false
        );

        self::assertMethodNotAllowedResponse($response, 'OPTIONS, GET');
    }

    public function testTryToUpdateRelationshipForUsers()
    {
        $response = $this->patchRelationship(
            ['entity' => 'customers', 'id' => '<toString(@customer3->id)>', 'association' => 'users'],
            [],
            [],
            false
        );

        self::assertMethodNotAllowedResponse($response, 'OPTIONS, GET');
    }

    public function testTryToDeleteRelationshipForUsers()
    {
        $response = $this->deleteRelationship(
            ['entity' => 'customers', 'id' => '<toString(@customer3->id)>', 'association' => 'users'],
            [],
            [],
            false
        );

        self::assertMethodNotAllowedResponse($response, 'OPTIONS, GET');
    }

    public function testGetRelationshipForGroup()
    {
        $response = $this->getRelationship(
            ['entity' => 'customers', 'id' => '<toString(@customer3->id)>', 'association' => 'group']
        );

        $this->assertResponseContains(
            [
                'data' => ['type' => 'customergroups', 'id' => '<toString(@customer_group1->id)>']
            ],
            $response
        );
    }

    public function testTryToUpdateRelationshipForGroup()
    {
        $response = $this->patchRelationship(
            ['entity' => 'customers', 'id' => '<toString(@customer3->id)>', 'association' => 'group'],
            [],
            [],
            false
        );

        self::assertMethodNotAllowedResponse($response, 'OPTIONS, GET');
    }

    public function testTryToGetSubresourceForOrganization()
    {
        $response = $this->getSubresource(
            ['entity' => 'customers', 'id' => '<toString(@customer3->id)>', 'association' => 'organization'],
            [],
            [],
            false
        );
        $this->assertUnsupportedSubresourceResponse($response);
    }

    public function testTryToGetRelationshipForOrganization()
    {
        $response = $this->getRelationship(
            ['entity' => 'customers', 'id' => '<toString(@customer3->id)>', 'association' => 'organization'],
            [],
            [],
            false
        );
        $this->assertUnsupportedSubresourceResponse($response);
    }

    public function testTryToGetSubresourceForOwner()
    {
        $response = $this->getSubresource(
            ['entity' => 'customers', 'id' => '<toString(@customer3->id)>', 'association' => 'owner'],
            [],
            [],
            false
        );
        $this->assertUnsupportedSubresourceResponse($response);
    }

    public function testTryToGetRelationshipForOwner()
    {
        $response = $this->getRelationship(
            ['entity' => 'customers', 'id' => '<toString(@customer3->id)>', 'association' => 'owner'],
            [],
            [],
            false
        );
        $this->assertUnsupportedSubresourceResponse($response);
    }

    public function testTryToUpdateRelationshipForOwner()
    {
        $response = $this->patchRelationship(
            ['entity' => 'customers', 'id' => '<toString(@customer3->id)>', 'association' => 'owner'],
            [],
            [],
            false
        );
        $this->assertUnsupportedSubresourceResponse($response);
    }

    public function testGetSubresourceForParent()
    {
        $response = $this->getSubresource(
            ['entity' => 'customers', 'id' => '@customer3->id', 'association' => 'parent']
        );

        $this->assertResponseContains(
            [
                'data' => [
                    'type'       => 'customers',
                    'id'         => '<toString(@customer1->id)>',
                    'attributes' => [
                        'name' => 'Customer 1'
                    ]
                ]
            ],
            $response
        );
    }

    public function testGetRelationshipForParent()
    {
        $response = $this->getRelationship(
            ['entity' => 'customers', 'id' => '@customer3->id', 'association' => 'parent']
        );

        $this->assertResponseContains(
            [
                'data' => ['type' => 'customers', 'id' => '<toString(@customer1->id)>']
            ],
            $response
        );
    }

    public function testTryToUpdateRelationshipForParent()
    {
        $response = $this->patchRelationship(
            ['entity' => 'customers', 'id' => '<toString(@customer3->id)>', 'association' => 'parent'],
            [],
            [],
            false
        );

        self::assertMethodNotAllowedResponse($response, 'OPTIONS, GET');
    }

    public function testGetSubresourceForChildren()
    {
        $response = $this->getSubresource(
            ['entity' => 'customers', 'id' => '@customer1->id', 'association' => 'children']
        );

        $this->assertResponseContains(
            [
                'data' => [
                    [
                        'type'       => 'customers',
                        'id'         => '<toString(@customer3->id)>',
                        'attributes' => [
                            'name' => 'Customer 3'
                        ]
                    ],
                    [
                        'type'       => 'customers',
                        'id'         => '<toString(@customer4->id)>',
                        'attributes' => [
                            'name' => 'Customer 4'
                        ]
                    ]
                ]
            ],
            $response
        );
    }

    public function testGetRelationshipForChildren()
    {
        $response = $this->getRelationship(
            ['entity' => 'customers', 'id' => '<toString(@customer1->id)>', 'association' => 'children']
        );

        $this->assertResponseContains(
            [
                'data' => [
                    ['type' => 'customers', 'id' => '<toString(@customer3->id)>'],
                    ['type' => 'customers', 'id' => '<toString(@customer4->id)>']
                ]
            ],
            $response
        );
    }

    public function testTryToAddRelationshipForChildren()
    {
        $response = $this->postRelationship(
            ['entity' => 'customers', 'id' => '<toString(@customer3->id)>', 'association' => 'children'],
            [],
            [],
            false
        );

        self::assertMethodNotAllowedResponse($response, 'OPTIONS, GET');
    }

    public function testTryToUpdateRelationshipForChildren()
    {
        $response = $this->patchRelationship(
            ['entity' => 'customers', 'id' => '<toString(@customer3->id)>', 'association' => 'children'],
            [],
            [],
            false
        );

        self::assertMethodNotAllowedResponse($response, 'OPTIONS, GET');
    }

    public function testTryToDeleteRelationshipForChildren()
    {
        $response = $this->deleteRelationship(
            ['entity' => 'customers', 'id' => '<toString(@customer3->id)>', 'association' => 'children'],
            [],
            [],
            false
        );

        self::assertMethodNotAllowedResponse($response, 'OPTIONS, GET');
    }

    public function testGetSubresourceForAddresses()
    {
        $response = $this->getSubresource(
            ['entity' => 'customers', 'id' => '<toString(@customer1->id)>', 'association' => 'addresses']
        );

        $this->assertResponseContains(
            [
                'data' => [
                    [
                        'type'       => 'customeraddresses',
                        'id'         => '<toString(@customer_address1->id)>',
                        'attributes' => [
                            'label'   => 'Address 1',
                            'primary' => true
                        ]
                    ],
                    [
                        'type'       => 'customeraddresses',
                        'id'         => '<toString(@customer_address2->id)>',
                        'attributes' => [
                            'label'   => 'Address 2',
                            'primary' => false
                        ]
                    ]
                ]
            ],
            $response
        );
    }

    public function testGetRelationshipForAddresses()
    {
        $response = $this->getRelationship(
            ['entity' => 'customers', 'id' => '<toString(@customer1->id)>', 'association' => 'addresses']
        );

        $this->assertResponseContains(
            [
                'data' => [
                    ['type' => 'customeraddresses', 'id' => '<toString(@customer_address1->id)>'],
                    ['type' => 'customeraddresses', 'id' => '<toString(@customer_address2->id)>']
                ]
            ],
            $response
        );
    }

    public function testTryToAddRelationshipForAddresses()
    {
        $response = $this->postRelationship(
            ['entity' => 'customers', 'id' => '<toString(@customer1->id)>', 'association' => 'addresses'],
            [],
            [],
            false
        );

        self::assertMethodNotAllowedResponse($response, 'OPTIONS, GET');
    }

    public function testTryToUpdateRelationshipForAddresses()
    {
        $response = $this->patchRelationship(
            ['entity' => 'customers', 'id' => '<toString(@customer1->id)>', 'association' => 'addresses'],
            [],
            [],
            false
        );

        self::assertMethodNotAllowedResponse($response, 'OPTIONS, GET');
    }

    public function testTryToDeleteRelationshipForAddresses()
    {
        $response = $this->deleteRelationship(
            ['entity' => 'customers', 'id' => '<toString(@customer1->id)>', 'association' => 'addresses'],
            [],
            [],
            false
        );

        self::assertMethodNotAllowedResponse($response, 'OPTIONS, GET');
    }
}
