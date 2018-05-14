<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api\Frontend\RestJsonApi;

use Oro\Bundle\CustomerBundle\Tests\Functional\Api\DataFixtures\LoadTestCustomerUser;
use Oro\Bundle\CustomerBundle\Tests\Functional\Api\DataFixtures\LoadTestUser;
use Oro\Bundle\FrontendBundle\Tests\Functional\Api\FrontendRestJsonApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class ApiFirewallTest extends FrontendRestJsonApiTestCase
{
    protected function setUp()
    {
        $this->markTestSkipped('TODO: BAP-17065');
        $this->initClient();
        $this->setCurrentWebsite();
        $this->loadFixtures(
            [
                LoadTestUser::class,
                LoadTestCustomerUser::class
            ]
        );
    }

    public function testCustomerUserShouldBeAbleToLoginWithSameEmailAsBackendUser()
    {
        // get customer user API key
        $this->client->request(
            'POST',
            $this->getUrl('oro_frontend_rest_api_list', ['entity' => 'login']),
            [
                'meta' => [
                    'email'    => 'test@test.com',
                    'password' => 'test_password'
                ]
            ],
            [],
            ['CONTENT_TYPE' => self::JSON_API_CONTENT_TYPE]
        );
        $response = $this->client->getResponse();
        $output = json_decode($response->getContent(), true);
        $apiKey = $output['meta']['apiKey'];

        $response = $this->post(
            ['entity' => 'login'],
            ['meta'=> ['email' => 'test@test.com', 'password' => 'test_password']],
            array_merge(
                ['CONTENT_TYPE' => self::JSON_API_CONTENT_TYPE],
                self::generateWsseAuthHeader('test@test.com', $apiKey)
            ),
            false
        );

        self::assertApiResponseStatusCodeEquals($response, Response::HTTP_OK, 'login', 'post');
    }
}
