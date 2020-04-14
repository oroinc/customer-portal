<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api\Frontend\RestJsonApi;

use Oro\Bundle\CustomerBundle\Tests\Functional\Api\Frontend\DataFixtures\LoadCustomerData;
use Oro\Bundle\FrontendBundle\Tests\Functional\Api\FrontendRestJsonApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class CustomerForVisitorTest extends FrontendRestJsonApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->enableVisitor();
        $this->loadFixtures([LoadCustomerData::class]);
    }

    public function testTryToGetList()
    {
        $response = $this->cget(
            ['entity' => 'customers'],
            [],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_FORBIDDEN);
    }

    public function testTryToGetListFilteredByMineId()
    {
        $response = $this->cget(
            ['entity' => 'customers'],
            ['filter' => ['id' => 'mine']],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_FORBIDDEN);
    }

    public function testTryToGet()
    {
        $response = $this->get(
            ['entity' => 'customers', 'id' => '<toString(@customer->id)>'],
            [],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_FORBIDDEN);
    }

    public function testTryToGetByMineId()
    {
        $response = $this->get(
            ['entity' => 'customers', 'id' => 'mine'],
            [],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_FORBIDDEN);
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
            ['entity' => 'customers', 'id' => '<toString(@customer->id)>'],
            [],
            [],
            false
        );
        self::assertMethodNotAllowedResponse($response, 'OPTIONS, GET');
    }

    public function testTryToDelete()
    {
        $response = $this->delete(
            ['entity' => 'customers', 'id' => '<toString(@customer->id)>'],
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
            ['filter' => ['id' => '<toString(@customer->id)>']],
            [],
            false
        );
        self::assertMethodNotAllowedResponse($response, 'OPTIONS, GET');
    }
}
