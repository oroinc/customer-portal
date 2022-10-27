<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api\Frontend\RestJsonApi;

use Oro\Bundle\FrontendBundle\Tests\Functional\Api\FrontendRestJsonApiTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * The test case to test that 401 response are always returned for unauthorized requests.
 */
class NotAccessibleResourceForUnauthorizedTest extends FrontendRestJsonApiTestCase
{
    private const WWW_AUTHENTICATE_HEADER_VALUE = 'WSSE realm="Secured Frontend API", profile="UsernameToken"';

    public function testAccessGranted()
    {
        $response = $this->get(
            ['entity' => 'testapiunaccessiblemodel', 'id' => 'access_granted'],
            [],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_UNAUTHORIZED);
        self::assertSame('', $response->getContent());
        self::assertResponseHeader($response, 'WWW-Authenticate', self::WWW_AUTHENTICATE_HEADER_VALUE);
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
        self::assertSame('', $response->getContent());
        self::assertResponseHeader($response, 'WWW-Authenticate', self::WWW_AUTHENTICATE_HEADER_VALUE);
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
        self::assertSame('', $response->getContent());
        self::assertResponseHeader($response, 'WWW-Authenticate', self::WWW_AUTHENTICATE_HEADER_VALUE);
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
        self::assertSame('', $response->getContent());
        self::assertResponseHeader($response, 'WWW-Authenticate', self::WWW_AUTHENTICATE_HEADER_VALUE);
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
        self::assertSame('', $response->getContent());
        self::assertResponseHeader($response, 'WWW-Authenticate', self::WWW_AUTHENTICATE_HEADER_VALUE);
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
        self::assertSame('', $response->getContent());
        self::assertResponseHeader($response, 'WWW-Authenticate', self::WWW_AUTHENTICATE_HEADER_VALUE);
    }
}
