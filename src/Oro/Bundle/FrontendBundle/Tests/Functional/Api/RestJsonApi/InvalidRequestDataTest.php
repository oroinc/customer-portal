<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional\Api\RestJsonApi;

use Oro\Bundle\FrontendBundle\Tests\Functional\Api\FrontendRestJsonApiTestCase;
use Oro\Bundle\TestFrameworkBundle\Entity\TestProduct;
use Symfony\Component\HttpFoundation\Response;

class InvalidRequestDataTest extends FrontendRestJsonApiTestCase
{
    public function testInvalidJsonInRequestData()
    {
        $response = $this->request(
            'POST',
            $this->getUrl(
                $this->getListRouteName(),
                ['entity' => $this->getEntityType(TestProduct::class)]
            ),
            [],
            [],
            '{"data": {"type": test"}}'
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_BAD_REQUEST);
        self::assertResponseContentTypeEquals($response, 'application/json');
        self::assertEquals(
            ['code' => 400, 'message' => 'Bad Request'],
            self::jsonToArray($response->getContent())
        );
    }
}
