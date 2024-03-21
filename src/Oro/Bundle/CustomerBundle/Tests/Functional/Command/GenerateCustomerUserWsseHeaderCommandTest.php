<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Command;

use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserData;
use Oro\Bundle\FrontendBundle\Tests\Functional\ApiFrontend\FrontendRestJsonApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class GenerateCustomerUserWsseHeaderCommandTest extends FrontendRestJsonApiTestCase
{
    protected function setUp(): void
    {
        $this->initClient();
        $this->loadFixtures([LoadCustomerUserData::class]);
        $this->setCurrentWebsite();
    }

    private function getApiKey(string $email, string $password): string
    {
        $response = $this->post(
            ['entity' => 'login'],
            [
                'meta' => [
                    'email'    => $email,
                    'password' => $password
                ]
            ],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_OK);

        $content = self::jsonToArray($response->getContent());

        return $content['meta']['apiKey'];
    }

    public function testGenerateWsseHeader()
    {
        $apiKey = $this->getApiKey(LoadCustomerUserData::EMAIL, LoadCustomerUserData::PASSWORD);
        $result = self::runCommand('oro:customer-user:wsse:generate-header', [$apiKey]);

        self::assertStringContainsString(
            'To use WSSE authentication add following headers to the request:',
            $result
        );
        self::assertStringContainsString('Authorization: WSSE profile="UsernameToken"', $result);
        self::assertStringContainsString(
            'X-WSSE: UsernameToken Username="grzegorz.brzeczyszczykiewicz@example.com"',
            $result
        );

        preg_match_all('/X-WSSE:\s*(.*$)/im', $result, $header);

        $response = $this->cget(
            ['entity' => 'customers'],
            ['filter' => ['id' => 'mine']],
            [
                'HTTP_X-WSSE'        => $header[1][0],
                'HTTP_Authorization' => 'WSSE profile="UsernameToken"'
            ],
            false
        );

        self::assertResponseStatusCodeNotEquals($response, Response::HTTP_UNAUTHORIZED);
    }

    public function testGenerateWsseHeaderOnWrongApiKey()
    {
        $result = self::runCommand('oro:customer-user:wsse:generate-header', ['wrongKey']);

        self::assertStringNotContainsString('To use WSSE authentication add following headers', $result);
        self::assertStringNotContainsString('Authorization: WSSE profile="UsernameToken"', $result);
        self::assertStringContainsString('API key "wrongKey" does not exists', $result);
    }
}
