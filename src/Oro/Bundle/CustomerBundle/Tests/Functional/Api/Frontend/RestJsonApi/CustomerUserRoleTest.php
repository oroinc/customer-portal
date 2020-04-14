<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api\Frontend\RestJsonApi;

use Oro\Bundle\CustomerBundle\Tests\Functional\Api\Frontend\DataFixtures\LoadAdminCustomerUserData;
use Oro\Bundle\FrontendBundle\Tests\Functional\Api\FrontendRestJsonApiTestCase;

class CustomerUserRoleTest extends FrontendRestJsonApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures([
            LoadAdminCustomerUserData::class,
            '@OroCustomerBundle/Tests/Functional/Api/Frontend/DataFixtures/customer_user_role.yml'
        ]);
    }

    public function testGetList()
    {
        $response = $this->cget(
            ['entity' => 'customeruserroles'],
            ['page[size]' => 20]
        );

        $this->assertResponseContains('cget_customer_user_role.yml', $response);
    }

    public function testGet()
    {
        $response = $this->get(
            ['entity' => 'customeruserroles', 'id' => '<toString(@buyer->id)>']
        );

        $this->assertResponseContains('get_customer_user_role.yml', $response);
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
