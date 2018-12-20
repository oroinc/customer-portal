<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api\Frontend\RestJsonApi;

use Oro\Bundle\CustomerBundle\Tests\Functional\Api\Frontend\DataFixtures\LoadBuyerCustomerUserData;
use Oro\Bundle\FrontendBundle\Tests\Functional\Api\FrontendRestJsonApiTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class CustomerUserForBuyerTest extends FrontendRestJsonApiTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->loadFixtures([
            LoadBuyerCustomerUserData::class,
            '@OroCustomerBundle/Tests/Functional/Api/Frontend/DataFixtures/customer_user.yml'
        ]);
    }

    public function testGetListShouldReturnOnlyCurrentLoggedInUser()
    {
        $response = $this->cget(['entity' => 'customerusers']);

        $this->assertResponseContains(
            [
                'data' => [
                    ['type' => 'customerusers', 'id' => '<toString(@customer_user->id)>']
                ]
            ],
            $response
        );
    }

    public function testGetCurrentLoggedInUser()
    {
        $response = $this->get(
            ['entity' => 'customerusers', 'id' => '<toString(@customer_user->id)>']
        );

        $this->assertResponseContains(
            [
                'data' => ['type' => 'customerusers', 'id' => '<toString(@customer_user->id)>']
            ],
            $response
        );
    }

    public function testTryToGetFromChildCustomer()
    {
        $response = $this->get(
            ['entity' => 'customerusers', 'id' => '<toString(@customer_user1->id)>'],
            [],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_NOT_FOUND);
    }

    public function testTryToGetFromAnotherRootCustomer()
    {
        $response = $this->get(
            ['entity' => 'customerusers', 'id' => '<toString(@another_customer_user->id)>'],
            [],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_NOT_FOUND);
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

    public function testTryToUpdateCurrentLoggedInUser()
    {
        $customerUserId = $this->getReference('customer_user')->getId();

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

    public function testTryToUpdateAnotherUser()
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

    public function testTryToDeleteCurrentLoggedInUser()
    {
        $customerUserId = $this->getReference('customer_user')->getId();

        $response = $this->delete(
            ['entity' => 'customerusers', 'id' => $customerUserId],
            [],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_FORBIDDEN);
    }

    public function testTryToDeleteAnotherUser()
    {
        $customerUserId = $this->getReference('customer_user1')->getId();

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

    public function testTryToDeleteListForCurrentLoggedInUser()
    {
        $response = $this->cdelete(
            ['entity' => 'customerusers'],
            ['filter[id]' => '<toString(@customer_user->id)>'],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_FORBIDDEN);
    }
}
