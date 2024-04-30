<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional\OpenApi;

use Symfony\Component\Yaml\Yaml;

/**
 * @group regression
 */
class FrontendOpenApiTest extends FrontendOpenApiTestCase
{
    public function testGenerationOpenApiForFrontendRestJsonApiSuccess(): void
    {
        $result = self::runCommand('oro:api:doc:open-api:dump', ['--view' => 'frontend_rest_json_api'], true, true);
        self::assertStringContainsString('"openapi":"3.1.0"', $result);
    }

    public function testValidateGeneratedOpenApiForFrontendRestJsonApi(): void
    {
        $result = self::runCommand(
            'oro:api:doc:open-api:dump',
            [
                '--view'   => 'frontend_rest_json_api',
                '--format' => 'yaml',
                '--entity=addresstypes',
                '--entity=countries',
                '--entity=regions'
            ],
            false,
            true
        );
        $expected = Yaml::parse(file_get_contents(__DIR__ . '/data/frontend_rest_json_api.yml'));
        $actual = Yaml::parse($result);
        $this->assertOpenApiEquals($expected, $actual);
    }
}
