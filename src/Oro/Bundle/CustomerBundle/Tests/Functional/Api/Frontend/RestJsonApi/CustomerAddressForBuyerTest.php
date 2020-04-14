<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api\Frontend\RestJsonApi;

use Oro\Bundle\CustomerBundle\Tests\Functional\Api\Frontend\DataFixtures\LoadBuyerCustomerUserData;
use Oro\Bundle\FrontendBundle\Tests\Functional\Api\FrontendRestJsonApiTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class CustomerAddressForBuyerTest extends FrontendRestJsonApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures([
            LoadBuyerCustomerUserData::class,
            '@OroCustomerBundle/Tests/Functional/Api/Frontend/DataFixtures/customer_address.yml'
        ]);
    }

    public function testGetListShouldReturnOnlyAddressesForCustomerOfCurrentLoggedInUser()
    {
        $response = $this->cget(
            ['entity' => 'customeraddresses']
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

    public function testGetAddressForCustomerOfCurrentLoggedInUser()
    {
        $response = $this->get(
            ['entity' => 'customeraddresses', 'id' => '<toString(@customer_address1->id)>']
        );

        $this->assertResponseContains(
            [
                'data' => ['type' => 'customeraddresses', 'id' => '<toString(@customer_address1->id)>']
            ],
            $response
        );
    }

    public function testGetAddressForCustomerFromAnotherDepartment()
    {
        $response = $this->get(
            ['entity' => 'customeraddresses', 'id' => '<toString(@another_customer_address1->id)>'],
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

    public function testGetAddressForChildCustomer()
    {
        $response = $this->get(
            ['entity' => 'customeraddresses', 'id' => '<toString(@customer_address3->id)>'],
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
            ['entity' => 'customeraddresses'],
            'create_customer_address_min.yml',
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

    public function testTryToUpdateAddressForCustomerOfCurrentLoggedInUser()
    {
        $addressId = $this->getReference('customer_address1')->getId();

        $response = $this->patch(
            ['entity' => 'customeraddresses', 'id' => (string)$addressId],
            [
                'data' => [
                    'type'       => 'customeraddresses',
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

    public function testTryToUpdateAddressForCustomerFromAnotherDepartment()
    {
        $addressId = $this->getReference('another_customer_address1')->getId();

        $response = $this->patch(
            ['entity' => 'customeraddresses', 'id' => (string)$addressId],
            [
                'data' => [
                    'type'       => 'customeraddresses',
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

    public function testTryToUpdateAddressForChildCustomer()
    {
        $addressId = $this->getReference('customer_address3')->getId();

        $response = $this->patch(
            ['entity' => 'customeraddresses', 'id' => (string)$addressId],
            [
                'data' => [
                    'type'       => 'customeraddresses',
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

    public function testTryToDeleteAddressForCustomerOfCurrentLoggedInUser()
    {
        $addressId = $this->getReference('customer_address1')->getId();

        $response = $this->delete(
            ['entity' => 'customeraddresses', 'id' => (string)$addressId],
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

    public function testTryToDeleteAddressForCustomerFromAnotherDepartment()
    {
        $addressId = $this->getReference('another_customer_address1')->getId();

        $response = $this->delete(
            ['entity' => 'customeraddresses', 'id' => (string)$addressId],
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

    public function testTryToDeleteAddressForChildCustomer()
    {
        $addressId = $this->getReference('customer_address3')->getId();

        $response = $this->delete(
            ['entity' => 'customeraddresses', 'id' => (string)$addressId],
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
        $addressId = $this->getReference('customer_address1')->getId();

        $response = $this->cdelete(
            ['entity' => 'customeraddresses'],
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
