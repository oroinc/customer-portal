<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional\Command;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @group regression
 */
class ViewOptionForDumpApiDocCommands extends WebTestCase
{
    #[\Override]
    protected function setUp(): void
    {
        $this->initClient();
    }

    public function testViewOptionForApiDocDumpCommand(): void
    {
        $output = self::runCommand('api:doc:dump', ['--view' => 'frontend_rest_json_api']);
        self::assertStringContainsString('## /api/addresstypes', $output);
    }

    public function testViewOptionForApiSwaggerDumpCommand(): void
    {
        $output = self::runCommand('api:swagger:dump', ['--view' => 'frontend_rest_json_api']);
        self::assertStringContainsString('"path":"\/addresstypes"', $output);
    }
}
