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
class CustomerForBuyerTest extends FrontendRestJsonApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures([
            LoadBuyerCustomerUserData::class,
            '@OroCustomerBundle/Tests/Functional/Api/Frontend/DataFixtures/customer.yml'
        ]);
    }

    public function testGetList()
    {
        $response = $this->cget(['entity' => 'customers']);

        $this->assertResponseContains(
            [
                'data' => [
                    [
                        'type'       => 'customers',
                        'id'         => '<toString(@customer->id)>',
                        'attributes' => [
                            'name' => 'Customer'
                        ]
                    ]
                ]
            ],
            $response
        );
    }

    public function testGetListFilteredByMineId()
    {
        $response = $this->cget(
            ['entity' => 'customers'],
            ['filter' => ['id' => 'mine']]
        );

        $this->assertResponseContains(
            [
                'data' => [
                    [
                        'type'       => 'customers',
                        'id'         => '<toString(@customer->id)>',
                        'attributes' => [
                            'name' => 'Customer'
                        ]
                    ]
                ]
            ],
            $response
        );
    }

    public function testGet()
    {
        $response = $this->get(
            ['entity' => 'customers', 'id' => '<toString(@customer->id)>']
        );

        $this->assertResponseContains(
            [
                'data' => [
                    'type'       => 'customers',
                    'id'         => '<toString(@customer->id)>',
                    'attributes' => [
                        'name' => 'Customer'
                    ]
                ]
            ],
            $response
        );
    }

    public function testGetByMineId()
    {
        $response = $this->get(
            ['entity' => 'customers', 'id' => 'mine']
        );

        $this->assertResponseContains(
            [
                'data' => [
                    'type'       => 'customers',
                    'id'         => '<toString(@customer->id)>',
                    'attributes' => [
                        'name' => 'Customer'
                    ]
                ]
            ],
            $response
        );
    }

    public function testTryToGetWhenAccessDenied()
    {
        $response = $this->get(
            ['entity' => 'customers', 'id' => '<toString(@customer3->id)>'],
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

    public function testGetSubresourceForParent()
    {
        $response = $this->getSubresource(
            ['entity' => 'customers', 'id' => '@customer->id', 'association' => 'parent']
        );

        $this->assertResponseContains(
            [
                'data' => null
            ],
            $response
        );
    }

    public function testTryToGetSubresourceForParentWhenAccessDenied()
    {
        $response = $this->getSubresource(
            ['entity' => 'customers', 'id' => '@customer3->id', 'association' => 'parent'],
            [],
            [],
            false
        );
        $this->assertResponseValidationError(
            [
                'title'  => 'access denied exception',
                'detail' => 'No access to the parent entity.'
            ],
            $response,
            Response::HTTP_FORBIDDEN
        );
    }

    public function testGetSubresourceForChildren()
    {
        $response = $this->getSubresource(
            ['entity' => 'customers', 'id' => '@customer->id', 'association' => 'children']
        );

        $this->assertResponseContains(
            [
                'data' => []
            ],
            $response
        );
    }

    public function testTryToGetSubresourceForChildrenWhenAccessDenied()
    {
        $response = $this->getSubresource(
            ['entity' => 'customers', 'id' => '@customer1->id', 'association' => 'children'],
            [],
            [],
            false
        );
        $this->assertResponseValidationError(
            [
                'title'  => 'access denied exception',
                'detail' => 'No access to the parent entity.'
            ],
            $response,
            Response::HTTP_FORBIDDEN
        );
    }
}
