<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional\Api\RestJsonApi;

use Oro\Bundle\FrontendBundle\Tests\Functional\Api\FrontendRestJsonApiTestCase;

class AddressTypeTest extends FrontendRestJsonApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->enableVisitor();
        $this->loadVisitor();
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
