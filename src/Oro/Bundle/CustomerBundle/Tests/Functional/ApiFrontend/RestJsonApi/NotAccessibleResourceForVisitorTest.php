<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\ApiFrontend\RestJsonApi;

use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\FrontendBundle\Tests\Functional\ApiFrontend\FrontendRestJsonApiTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * The test case to test that 4xx responses for storefront visitors are correct.
 * @see \Oro\Bundle\CustomerBundle\Tests\Functional\Environment\Api\Processor\HandleSpecialIdentifiers
 */
class NotAccessibleResourceForVisitorTest extends FrontendRestJsonApiTestCase
{
    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->initializeVisitor();
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
        self::assertResponseStatusCodeEquals($response, Response::HTTP_FORBIDDEN);
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
        $this->assertResourceNotAccessibleResponse($response);
    }

    public function testNotAllowed()
    {
        $response = $this->get(
            ['entity' => 'testapiunaccessiblemodel', 'id' => 'not_allowed'],
            [],
            [],
            false
        );
        self::assertMethodNotAllowedResponse($response, 'OPTIONS, GET');
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
