<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Command;

use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserData;
use Oro\Bundle\FrontendBundle\Tests\Functional\Api\FrontendRestJsonApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class GenerateCustomerUserWsseHeaderCommandTest extends FrontendRestJsonApiTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->initClient();
        $this->loadFixtures([LoadCustomerUserData::class]);
        $this->setCurrentWebsite();
    }

    /**
     * @param string $email
     * @param string $password
     *
     * @return string
     */
    private function getApiKey($email, $password): string
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
        $content = self::jsonToArray($response->getContent());

        return $content['meta']['apiKey'];
    }

    public function testGenerateWsseHeader()
    {
        $apiKey = $this->getApiKey(LoadCustomerUserData::EMAIL, LoadCustomerUserData::PASSWORD);
        $result = $this->runCommand('oro:customer-user:wsse:generate-header', [$apiKey]);

        $this->assertStringContainsString('To use WSSE authentication add following headers to the request:', $result);
        $this->assertStringContainsString('Authorization: WSSE profile="UsernameToken"', $result);
        $this->assertStringContainsString(
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

        $this->assertResponseStatusCodeNotEquals($response, Response::HTTP_UNAUTHORIZED);
    }

    public function testGenerateWsseHeaderOnWrongApiKey()
    {
        $result = $this->runCommand('oro:customer-user:wsse:generate-header', ['wrongKey']);

        $this->assertStringNotContainsString('To use WSSE authentication add following headers', $result);
        $this->assertStringNotContainsString('Authorization: WSSE profile="UsernameToken"', $result);
        $this->assertStringContainsString('API key "wrongKey" does not exists', $result);
    }

    public function testGenerateWsseHeaderWithHelpOption()
    {
        $result = $this->runCommand('oro:customer-user:wsse:generate-header', ['--help']);

        $this->assertStringContainsString('Description: Generate X-WSSE HTTP header for a given API key', $result);
        $this->assertStringContainsString(
            '--firewall=FIREWALL Firewall name. [default: "frontend_api_wsse_secured"]',
            $result
        );
    }
}
