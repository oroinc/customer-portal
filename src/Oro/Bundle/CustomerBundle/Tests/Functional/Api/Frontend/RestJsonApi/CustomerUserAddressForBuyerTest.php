<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api\Frontend\RestJsonApi;

use Oro\Bundle\CustomerBundle\Tests\Functional\Api\Frontend\DataFixtures\LoadBuyerCustomerUserData;
use Oro\Bundle\FrontendBundle\Tests\Functional\Api\FrontendRestJsonApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class CustomerUserAddressForBuyerTest extends FrontendRestJsonApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures([
            LoadBuyerCustomerUserData::class,
            '@OroCustomerBundle/Tests/Functional/Api/Frontend/DataFixtures/customer_user_address.yml'
        ]);
    }

    public function testGetListShouldReturnOnlyAddressForCurrentLoggedInUser()
    {
        $response = $this->cget(
            ['entity' => 'customeruseraddresses']
        );

        $this->assertResponseContains(
            [
                'data' => [
                    ['type' => 'customeruseraddresses', 'id' => '<toString(@customer_user_address->id)>']
                ]
            ],
            $response
        );
    }

    public function testGetAddressForCurrentLoggedInUser()
    {
        $response = $this->get(
            ['entity' => 'customeruseraddresses', 'id' => '<toString(@customer_user_address->id)>']
        );

        $this->assertResponseContains(
            [
                'data' => ['type' => 'customeruseraddresses', 'id' => '<toString(@customer_user_address->id)>']
            ],
            $response
        );
    }

    public function testGetAddressForAnotherUser()
    {
        $response = $this->get(
            ['entity' => 'customeruseraddresses', 'id' => '<toString(@customer_user_address1->id)>'],
            [],
            [],
            false
        );
        $this->assertResponseValidationError(
            [
                'title'  => 'access denied exception',
                'detail' => 'No access to the entity.'
            ],
            $response,
            Response::HTTP_FORBIDDEN
        );
    }

    public function testTryToCreate()
    {
        $response = $this->post(
            ['entity' => 'customeruseraddresses'],
            'create_customer_user_address_min.yml',
            [],
            false
        );
        $this->assertResponseValidationError(
            [
                'title'  => 'access denied exception',
                'detail' => 'No access to this type of entities.'
            ],
            $response,
            Response::HTTP_FORBIDDEN
        );
    }

    public function testTryToUpdateAddressForCurrentLoggedInUser()
    {
        $addressId = $this->getReference('customer_user_address')->getId();

        $response = $this->patch(
            ['entity' => 'customeruseraddresses', 'id' => (string)$addressId],
            [
                'data' => [
                    'type'       => 'customeruseraddresses',
                    'id'         => (string)$addressId,
                    'attributes' => [
                        'label' => 'Updated Address'
                    ]
                ]
            ],
            [],
            false
        );
        $this->assertResponseValidationError(
            [
                'title'  => 'access denied exception',
                'detail' => 'No access to this type of entities.'
            ],
            $response,
            Response::HTTP_FORBIDDEN
        );
    }

    public function testTryToUpdateAddressForAnotherUser()
    {
        $addressId = $this->getReference('customer_user_address1')->getId();

        $response = $this->patch(
            ['entity' => 'customeruseraddresses', 'id' => (string)$addressId],
            [
                'data' => [
                    'type'       => 'customeruseraddresses',
                    'id'         => (string)$addressId,
                    'attributes' => [
                        'label' => 'Updated Address'
                    ]
                ]
            ],
            [],
            false
        );
        $this->assertResponseValidationError(
            [
                'title'  => 'access denied exception',
                'detail' => 'No access to this type of entities.'
            ],
            $response,
            Response::HTTP_FORBIDDEN
        );
    }

    public function testTryToDeleteAddressForCurrentLoggedInUser()
    {
        $addressId = $this->getReference('customer_user_address')->getId();

        $response = $this->delete(
            ['entity' => 'customeruseraddresses', 'id' => $addressId],
            [],
            [],
            false
        );
        $this->assertResponseValidationError(
            [
                'title'  => 'access denied exception',
                'detail' => 'No access to this type of entities.'
            ],
            $response,
            Response::HTTP_FORBIDDEN
        );
    }

    public function testTryToDeleteAddressForAnotherUser()
    {
        $addressId = $this->getReference('customer_user_address1')->getId();

        $response = $this->delete(
            ['entity' => 'customeruseraddresses', 'id' => $addressId],
            [],
            [],
            false
        );
        $this->assertResponseValidationError(
            [
                'title'  => 'access denied exception',
                'detail' => 'No access to this type of entities.'
            ],
            $response,
            Response::HTTP_FORBIDDEN
        );
    }

    public function testTryToDeleteList()
    {
        $addressId = $this->getReference('customer_user_address1')->getId();

        $response = $this->cdelete(
            ['entity' => 'customeruseraddresses'],
            ['filter' => ['id' => (string)$addressId]],
            [],
            false
        );
        $this->assertResponseValidationError(
            [
                'title'  => 'access denied exception',
                'detail' => 'No access to this type of entities.'
            ],
            $response,
            Response::HTTP_FORBIDDEN
        );
    }
}
