<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api\Frontend\RestJsonApi;

use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\FrontendBundle\Tests\Functional\Api\FrontendRestJsonApiTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * The test case to test that 4xx responses for storefront visitors are correct.
 * For storefront visitors access denied errors should be returned as 401 (Unauthorized).
 * @see \Oro\Bundle\CustomerBundle\Tests\Functional\Environment\Api\Processor\HandleSpecialIdentifiers
 */
class NotAccessibleResourceForVisitorTest extends FrontendRestJsonApiTestCase
{
    /**
     * @param Response $response
     */
    public static function assertUnauthorizedResponse(Response $response)
    {
        self::assertResponseStatusCodeEquals($response, Response::HTTP_UNAUTHORIZED);
        self::assertEquals(
            [
                'WSSE realm="Secured Frontend API", profile="UsernameToken"'
            ],
            $response->headers->get('WWW-Authenticate', [], false)
        );
    }

    public function testAccessGranted()
    {
        $response = $this->get(
            ['entity' => 'testapiunaccessiblemodel', 'id' => 'access_granted']
        );
        $this->assertResponseContains(
            [
                'data' => [
                    'type'       => 'testapiunaccessiblemodel',
                    'id'         => '1',
                    'attributes' => [
                        'name' => 'Access granted. Token: ' . AnonymousCustomerUserToken::class
                    ]
                ]
            ],
            $response
        );
    }

    public function testAccessDenied()
    {
        $response = $this->get(
            ['entity' => 'testapiunaccessiblemodel', 'id' => 'access_denied'],
            [],
            [],
            false
        );
        self::assertUnauthorizedResponse($response);
    }

    public function testForbidden()
    {
        $response = $this->get(
            ['entity' => 'testapiunaccessiblemodel', 'id' => 'forbidden'],
            [],
            [],
            false
        );
        self::assertUnauthorizedResponse($response);
    }

    public function testNotFound()
    {
        $response = $this->get(
            ['entity' => 'testapiunaccessiblemodel', 'id' => 'not_found'],
            [],
            [],
            false
        );
        $this->assertResponseValidationError(
            ['title' => 'not found http exception'],
            $response,
            Response::HTTP_NOT_FOUND
        );
    }

    public function testNotAccessible()
    {
        $response = $this->get(
            ['entity' => 'testapiunaccessiblemodel', 'id' => 'not_accessible'],
            [],
            [],
            false
        );
        $this->assertResponseValidationError(
            ['title' => 'resource not accessible exception'],
            $response,
            Response::HTTP_NOT_FOUND
        );
    }

    public function testNotAllowed()
    {
        $response = $this->get(
            ['entity' => 'testapiunaccessiblemodel', 'id' => 'not_allowed'],
            [],
            [],
            false
        );
        self::assertMethodNotAllowedResponse($response, 'GET');
    }

    public function testUnexpectedError()
    {
        $response = $this->get(
            ['entity' => 'testapiunaccessiblemodel', 'id' => 'another'],
            [],
            [],
            false
        );
        $this->assertResponseValidationError(
            ['title' => 'runtime exception'],
            $response,
            Response::HTTP_INTERNAL_SERVER_ERROR
        );
    }
}
