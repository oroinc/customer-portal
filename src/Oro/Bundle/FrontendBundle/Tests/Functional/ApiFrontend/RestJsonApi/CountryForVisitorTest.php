<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional\ApiFrontend\RestJsonApi;

use Oro\Bundle\FrontendBundle\Tests\Functional\ApiFrontend\FrontendRestJsonApiTestCase;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class CountryForVisitorTest extends FrontendRestJsonApiTestCase
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
            ['entity' => 'countries'],
            ['filter' => ['id' => 'IL']]
        );
        $this->assertResponseContains('cget_country.yml', $response);
    }

    public function testGet()
    {
        $response = $this->get(
            ['entity' => 'countries', 'id' => 'IL']
        );
        $this->assertResponseContains('get_country.yml', $response);
    }

    public function testTryToCreate()
    {
        $response = $this->post(
            ['entity' => 'countries'],
            [],
            [],
            false
        );
        self::assertAllowResponseHeader($response, 'OPTIONS, GET');
    }

    public function testTryToUpdate()
    {
        $response = $this->patch(
            ['entity' => 'countries', 'id' => 'GB'],
            [],
            [],
            false
        );
        self::assertAllowResponseHeader($response, 'OPTIONS, GET');
    }

    public function testTryToDelete()
    {
        $response = $this->delete(
            ['entity' => 'countries', 'id' => 'GB'],
            [],
            [],
            false
        );
        self::assertAllowResponseHeader($response, 'OPTIONS, GET');
    }

    public function testTryToDeleteList()
    {
        $response = $this->cdelete(
            ['entity' => 'countries', 'id' => 'GB'],
            [],
            [],
            false
        );
        self::assertAllowResponseHeader($response, 'OPTIONS, GET');
    }

    public function testGetSubresourceRegions()
    {
        $response = $this->getSubresource(
            ['entity' => 'countries', 'id' => 'IL', 'association' => 'regions']
        );
        $this->assertResponseContains('get_country_regions.yml', $response);
    }

    public function testGetRelationshipRegions()
    {
        $response = $this->getRelationship(
            ['entity' => 'countries', 'id' => 'IL', 'association' => 'regions']
        );
        $this->assertResponseContains('get_country_regions_id.yml', $response);
    }

    public function testTryToUpdateRelationshipRegions()
    {
        $response = $this->patchRelationship(
            ['entity' => 'countries', 'id' => 'IL', 'association' => 'regions'],
            [],
            [],
            false
        );
        self::assertAllowResponseHeader($response, 'OPTIONS, GET');
    }

    public function testTryToAddRelationshipRegions()
    {
        $response = $this->postRelationship(
            ['entity' => 'countries', 'id' => 'IL', 'association' => 'regions'],
            [],
            [],
            false
        );
        self::assertAllowResponseHeader($response, 'OPTIONS, GET');
    }

    public function testTryToDeleteRelationshipRegions()
    {
        $response = $this->deleteRelationship(
            ['entity' => 'countries', 'id' => 'IL', 'association' => 'regions'],
            [],
            [],
            false
        );
        self::assertAllowResponseHeader($response, 'OPTIONS, GET');
    }
}
