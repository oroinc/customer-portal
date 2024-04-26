<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\OpenApi;

use Oro\Bundle\FrontendBundle\Tests\Functional\OpenApi\FrontendOpenApiTestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * @group regression
 */
class LoginOpenApiTest extends FrontendOpenApiTestCase
{
    public function testValidateGeneratedOpenApiForFrontendRestJsonApiLogin(): void
    {
        $result = self::runCommand(
            'oro:api:doc:open-api:dump',
            ['--view' => 'frontend_rest_json_api', '--format' => 'yaml', '--entity' => 'login'],
            false,
            true
        );
        $expected = Yaml::parse(file_get_contents(__DIR__ . '/data/frontend_rest_json_api_login.yml'));
        $actual = Yaml::parse($result);
        $this->assertOpenApiEquals($expected, $actual);
    }
}
