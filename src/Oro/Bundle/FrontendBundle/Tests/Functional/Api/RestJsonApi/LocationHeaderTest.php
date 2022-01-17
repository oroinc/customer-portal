<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional\Api\RestJsonApi;

use Oro\Bundle\FrontendBundle\Tests\Functional\Api\FrontendRestJsonApiTestCase;
use Oro\Bundle\TestFrameworkBundle\Entity\TestProduct;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @dbIsolationPerTest
 */
class LocationHeaderTest extends FrontendRestJsonApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->enableVisitor();
        $this->loadVisitor();
    }

    public function testPostShouldReturnLocationHeader()
    {
        $entityType = $this->getEntityType(TestProduct::class);
        $response = $this->post(
            ['entity' => $entityType],
            ['data' => ['type' => $entityType, 'attributes' => ['name' => 'test']]],
            ['HTTP_HATEOAS' => true]
        );
        self::assertTrue($response->headers->has('Location'));
        $locationUrl = $this->getUrl(
            'oro_frontend_rest_api_item',
            ['entity' => $entityType, 'id' => $this->getResourceId($response)],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        self::assertEquals($locationUrl, $response->headers->get('Location'));
        // test that "self" link the same as "Location" header
        $content = self::jsonToArray($response->getContent());
        self::assertEquals($locationUrl, $content['data']['links']['self']);
    }

    public function testPostShouldNotReturnLocationHeaderIfNotSuccess()
    {
        $entityType = $this->getEntityType(TestProduct::class);
        $response = $this->post(
            ['entity' => $entityType],
            ['data' => ['type' => $entityType, 'attributes' => ['unknown' => 'test']]],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_BAD_REQUEST);
        self::assertFalse($response->headers->has('Location'));
    }
}
