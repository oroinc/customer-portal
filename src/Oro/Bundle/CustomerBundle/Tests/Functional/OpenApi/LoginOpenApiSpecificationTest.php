<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\OpenApi;

use Oro\Bundle\FrontendBundle\Tests\Functional\OpenApi\FrontendOpenApiSpecificationTestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * @group regression
 */
class LoginOpenApiSpecificationTest extends FrontendOpenApiSpecificationTestCase
{
    public function testValidateGeneratedOpenApiSpecificationForFrontendRestJsonApiLogin(): void
    {
        $result = self::runCommand(
            'oro:api:doc:open-api:dump',
            ['--view' => 'frontend_rest_json_api', '--format' => 'yaml', '--entity' => 'login'],
            false,
            true
        );
        $expected = Yaml::parse(file_get_contents(__DIR__ . '/data/frontend_rest_json_api_login.yml'));
        $actual = Yaml::parse($result);
        $this->assertOpenApiSpecificationEquals($expected, $actual);
    }
}
