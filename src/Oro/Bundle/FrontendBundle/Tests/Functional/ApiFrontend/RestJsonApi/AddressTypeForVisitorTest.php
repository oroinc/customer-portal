<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional\ApiFrontend\RestJsonApi;

use Oro\Bundle\FrontendBundle\Tests\Functional\ApiFrontend\FrontendRestJsonApiTestCase;

class AddressTypeForVisitorTest extends FrontendRestJsonApiTestCase
{
    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->initializeVisitor();
    }

    public function testGetList()
    {
        $response = $this->cget(
            ['entity' => 'addresstypes']
        );
        $this->assertResponseContains('cget_address_type.yml', $response);
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
