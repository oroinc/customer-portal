<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\ApiFrontend\RestJsonApi;

use Oro\Bundle\CustomerBundle\Tests\Functional\Api\DataFixtures\LoadCustomerUserData;
use Oro\Bundle\FrontendBundle\Tests\Functional\ApiFrontend\FrontendRestJsonApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class ApiFirewallTest extends FrontendRestJsonApiTestCase
{
    #[\Override]
    protected function setUp(): void
    {
        $this->initClient();
        $this->loadFixtures([LoadCustomerUserData::class]);
    }

    public function testCustomerUserShouldBeAbleToLoginWithSameEmailAsBackendUser()
    {
        // get customer user API key
        $response = $this->request(
            'POST',
            $this->getUrl('oro_frontend_rest_api_list', ['entity' => 'login']),
            [
                'meta' => [
                    'email'    => 'test@test.com',
                    'password' => 'test_password'
                ]
            ]
        );
        $output = self::jsonToArray($response->getContent());
        $apiKey = $output['meta']['apiKey'];

        $response = $this->post(
            ['entity' => 'login'],
            ['meta' => ['email' => 'test@test.com', 'password' => 'test_password']],
            self::generateWsseAuthHeader('test@test.com', $apiKey),
            false
        );

        self::assertApiResponseStatusCodeEquals($response, Response::HTTP_OK, 'login', 'post');
    }
}
