<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\ApiFrontend\RestJsonApi;

use Oro\Bundle\FrontendBundle\Tests\Functional\ApiFrontend\FrontendRestJsonApiTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * The test case to test that 401 response are always returned for unauthenticated requests.
 */
class NotAccessibleResourceForUnauthenticatedTest extends FrontendRestJsonApiTestCase
{
    private const WWW_AUTHENTICATE_HEADER_VALUE = 'Bearer';

    public function testTryToGetOptionsForList(): void
    {
        $response = $this->options(
            $this->getListRouteName(),
            ['entity' => 'testapiunaccessiblemodel'],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_NOT_FOUND);
    }

    public function testOptionsForItem(): void
    {
        $response = $this->options(
            $this->getItemRouteName(),
            ['entity' => 'testapiunaccessiblemodel', 'id' => 'test']
        );
        self::assertAllowResponseHeader($response, 'OPTIONS, GET');
    }

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

    public function testUnauthorizedResponseHasSecurityHeaders(): void
    {
        $response = $this->get(
            ['entity' => 'testapiunaccessiblemodel', 'id' => 'access_granted'],
            [],
            [],
            false
        );

        self::assertResponseStatusCodeEquals($response, Response::HTTP_UNAUTHORIZED);
        self::assertResponseHeader($response, 'X-Content-Type-Options', 'nosniff');
        self::assertTrue($response->headers->has('X-Frame-Options'));
        self::assertTrue($response->headers->has('X-XSS-Protection'));
    }
}
