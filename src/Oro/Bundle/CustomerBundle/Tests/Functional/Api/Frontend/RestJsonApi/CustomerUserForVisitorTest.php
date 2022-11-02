<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api\Frontend\RestJsonApi;

use Oro\Bundle\CustomerBundle\Tests\Functional\Api\Frontend\DataFixtures\LoadCustomerData;
use Oro\Bundle\FrontendBundle\Tests\Functional\Api\FrontendRestJsonApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class CustomerUserForVisitorTest extends FrontendRestJsonApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->enableVisitor();
        $this->loadFixtures([
            LoadCustomerData::class,
            '@OroCustomerBundle/Tests/Functional/Api/Frontend/DataFixtures/customer_user.yml'
        ]);
    }

    public function testTryToGetList()
    {
        $response = $this->cget(
            ['entity' => 'customerusers'],
            [],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_FORBIDDEN);
    }

    public function testTryToGetListFilteredByMineId()
    {
        $response = $this->cget(
            ['entity' => 'customerusers'],
            ['filter' => ['id' => 'mine']],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_FORBIDDEN);
    }

    public function testTryToGetListFilteredByMineCustomerId()
    {
        $response = $this->cget(
            ['entity' => 'customerusers'],
            ['filter' => ['customer' => 'mine']],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_FORBIDDEN);
    }

    public function testTryToGet()
    {
        $response = $this->get(
            ['entity' => 'customerusers', 'id' => '<toString(@customer_user1->id)>'],
            [],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_FORBIDDEN);
    }

    public function testTryToGetByMineId()
    {
        $response = $this->get(
            ['entity' => 'customerusers', 'id' => 'mine'],
            [],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_FORBIDDEN);
    }

    public function testTryToCreate()
    {
        $response = $this->post(
            ['entity' => 'customerusers'],
            'create_customer_user_min.yml',
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_FORBIDDEN);
    }

    public function testTryToUpdate()
    {
        $customerUserId = $this->getReference('customer_user1')->getId();

        $response = $this->patch(
            ['entity' => 'customerusers', 'id' => $customerUserId],
            [
                'data' => [
                    'type'       => 'customerusers',
                    'id'         => (string)$customerUserId,
                    'attributes' => [
                        'firstName' => 'Updated First Name'
                    ]
                ]
            ],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_FORBIDDEN);
    }

    public function testTryToDelete()
    {
        $customerUserId = $this->getReference('customer_user2')->getId();

        $response = $this->delete(
            ['entity' => 'customerusers', 'id' => $customerUserId],
            [],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_FORBIDDEN);
    }

    public function testTryToDeleteList()
    {
        $response = $this->cdelete(
            ['entity' => 'customerusers'],
            ['filter[email]' => 'user2@example.com'],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_FORBIDDEN);
    }
}
