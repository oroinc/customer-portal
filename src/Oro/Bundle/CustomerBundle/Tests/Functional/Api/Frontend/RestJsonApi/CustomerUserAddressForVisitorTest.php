<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api\Frontend\RestJsonApi;

use Oro\Bundle\CustomerBundle\Tests\Functional\Api\Frontend\DataFixtures\LoadCustomerData;
use Oro\Bundle\CustomerBundle\Tests\Functional\Api\Frontend\DataFixtures\LoadCustomerUserData;
use Oro\Bundle\FrontendBundle\Tests\Functional\Api\FrontendRestJsonApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class CustomerUserAddressForVisitorTest extends FrontendRestJsonApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->enableVisitor();
        $this->loadFixtures([
            LoadCustomerData::class,
            LoadCustomerUserData::class,
            '@OroCustomerBundle/Tests/Functional/Api/Frontend/DataFixtures/customer_user_address.yml'
        ]);
    }

    public function testTryToGetList()
    {
        $response = $this->cget(
            ['entity' => 'customeruseraddresses'],
            [],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_FORBIDDEN);
    }

    public function testTryToGet()
    {
        $response = $this->get(
            ['entity' => 'customeruseraddresses', 'id' => '<toString(@another_customer_user_address1->id)>'],
            [],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_FORBIDDEN);
    }

    public function testTryToCreate()
    {
        $response = $this->post(
            ['entity' => 'customeruseraddresses'],
            'create_customer_user_address_min.yml',
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_FORBIDDEN);
    }

    public function testTryToUpdate()
    {
        $response = $this->patch(
            ['entity' => 'customeruseraddresses', 'id' => '<toString(@another_customer_user_address1->id)>'],
            'update_customer_user_address.yml',
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_FORBIDDEN);
    }

    public function testTryToDelete()
    {
        $addressId = $this->getReference('another_customer_user_address1')->getId();

        $response = $this->delete(
            ['entity' => 'customeruseraddresses', 'id' => $addressId],
            [],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_FORBIDDEN);
    }

    public function testTryToDeleteList()
    {
        $addressId = $this->getReference('another_customer_user_address1')->getId();

        $response = $this->cdelete(
            ['entity' => 'customeruseraddresses'],
            ['filter' => ['id' => (string)$addressId]],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_FORBIDDEN);
    }
}
