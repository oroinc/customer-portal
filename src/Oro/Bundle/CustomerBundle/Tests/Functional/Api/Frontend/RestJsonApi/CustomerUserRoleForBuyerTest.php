<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api\Frontend\RestJsonApi;

use Oro\Bundle\CustomerBundle\Tests\Functional\Api\Frontend\DataFixtures\LoadBuyerCustomerUserData;
use Oro\Bundle\FrontendBundle\Tests\Functional\Api\FrontendRestJsonApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class CustomerUserRoleForBuyerTest extends FrontendRestJsonApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures([
            LoadBuyerCustomerUserData::class,
            '@OroCustomerBundle/Tests/Functional/Api/Frontend/DataFixtures/customer_user_role.yml'
        ]);
    }

    public function testTryToGetList()
    {
        $response = $this->cget(
            ['entity' => 'customeruserroles'],
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

    public function testTryToGet()
    {
        $response = $this->get(
            ['entity' => 'customeruserroles', 'id' => '<toString(@buyer->id)>'],
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

    public function testTryToCreate()
    {
        $response = $this->post(
            ['entity' => 'customeruserroles'],
            [],
            [],
            false
        );
        self::assertMethodNotAllowedResponse($response, 'OPTIONS, GET');
    }

    public function testTryToUpdate()
    {
        $response = $this->patch(
            ['entity' => 'customeruserroles', 'id' => '<toString(@buyer->id)>'],
            [],
            [],
            false
        );
        self::assertMethodNotAllowedResponse($response, 'OPTIONS, GET');
    }

    public function testTryToDelete()
    {
        $response = $this->delete(
            ['entity' => 'customeruserroles', 'id' => '<toString(@buyer->id)>'],
            [],
            [],
            false
        );
        self::assertMethodNotAllowedResponse($response, 'OPTIONS, GET');
    }

    public function testTryToDeleteList()
    {
        $response = $this->cdelete(
            ['entity' => 'customeruserroles'],
            ['filter' => ['id' => '<toString(@buyer->id)>']],
            [],
            false
        );
        self::assertMethodNotAllowedResponse($response, 'OPTIONS, GET');
    }
}
