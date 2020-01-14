<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api\Frontend\RestJsonApi;

use Oro\Bundle\FrontendBundle\Tests\Functional\Api\FrontendRestJsonApiTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * The test case to test that 401 response are always returned for unauthorized requests.
 */
class NotAccessibleResourceForUnauthorizedTest extends FrontendRestJsonApiTestCase
{
    public function testAccessGranted()
    {
        $response = $this->get(
            ['entity' => 'testapiunaccessiblemodel', 'id' => 'access_granted'],
            [],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_UNAUTHORIZED);
    }

    public function testAccessDenied()
    {
        $response = $this->get(
            ['entity' => 'testapiunaccessiblemodel', 'id' => 'access_denied'],
            [],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_UNAUTHORIZED);
    }

    public function testNotFound()
    {
        $response = $this->get(
            ['entity' => 'testapiunaccessiblemodel', 'id' => 'not_found'],
            [],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_UNAUTHORIZED);
    }

    public function testNotAccessible()
    {
        $response = $this->get(
            ['entity' => 'testapiunaccessiblemodel', 'id' => 'not_accessible'],
            [],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_UNAUTHORIZED);
    }

    public function testNotAllowed()
    {
        $response = $this->get(
            ['entity' => 'testapiunaccessiblemodel', 'id' => 'not_allowed'],
            [],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_UNAUTHORIZED);
    }

    public function testUnexpectedError()
    {
        $response = $this->get(
            ['entity' => 'testapiunaccessiblemodel', 'id' => 'another'],
            [],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_UNAUTHORIZED);
    }
}
