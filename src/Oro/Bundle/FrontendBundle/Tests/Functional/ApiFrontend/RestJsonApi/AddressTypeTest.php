<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional\ApiFrontend\RestJsonApi;

use Oro\Bundle\CustomerBundle\Tests\Functional\ApiFrontend\DataFixtures\LoadAdminCustomerUserData;
use Oro\Bundle\FrontendBundle\Tests\Functional\ApiFrontend\FrontendRestJsonApiTestCase;

class AddressTypeTest extends FrontendRestJsonApiTestCase
{
    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures([LoadAdminCustomerUserData::class]);
    }

    public function testGetList()
    {
        $response = $this->cget(
            ['entity' => 'addresstypes']
        );
        $this->assertResponseContains('cget_address_type.yml', $response);
    }

    public function testGetListFilterBySeveralIds()
    {
        $response = $this->cget(
            ['entity' => 'addresstypes'],
            ['filter' => ['id' => 'billing,shipping']]
        );
        $this->assertResponseContains('cget_address_type_filter_ids.yml', $response);
    }

    public function testGet()
    {
        $response = $this->get(
            ['entity' => 'addresstypes', 'id' => 'shipping']
        );
        $this->assertResponseContains('get_address_type.yml', $response);
    }

    public function testTryToCreate()
    {
        $response = $this->post(
            ['entity' => 'addresstypes'],
            [],
            [],
            false
        );
        self::assertAllowResponseHeader($response, 'OPTIONS, GET');
    }

    public function testTryToUpdate()
    {
        $response = $this->patch(
            ['entity' => 'addresstypes', 'id' => 'shipping'],
            [],
            [],
            false
        );
        self::assertAllowResponseHeader($response, 'OPTIONS, GET');
    }

    public function testTryToDelete()
    {
        $response = $this->delete(
            ['entity' => 'addresstypes', 'id' => 'shipping'],
            [],
            [],
            false
        );
        self::assertAllowResponseHeader($response, 'OPTIONS, GET');
    }

    public function testTryToDeleteList()
    {
        $response = $this->cdelete(
            ['entity' => 'addresstypes', 'id' => 'shipping'],
            [],
            [],
            false
        );
        self::assertAllowResponseHeader($response, 'OPTIONS, GET');
    }
}
