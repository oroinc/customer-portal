<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional\Command;

use Oro\Bundle\FrontendBundle\Command\FrontendJsRoutingDumpCommand;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class FrontendJsRoutingDumpCommandTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient();
    }

    public function testExecute(): void
    {
        $result = $this->runCommand(FrontendJsRoutingDumpCommand::getDefaultName(), ['-vvv']);

        $this->assertNotEmpty($result);
        self::assertStringContainsString($this->getEndPath('frontend_routes', 'json'), $result);
    }

    public function testExecuteWithJsFormat(): void
    {
        $result = $this->runCommand(FrontendJsRoutingDumpCommand::getDefaultName(), ['-vvv', '--format=js']);

        $this->assertNotEmpty($result);
        self::assertStringContainsString($this->getEndPath('frontend_routes', 'js'), $result);
    }

    public function testExecuteWithCustomTarget(): void
    {
        $projectDir = $this->getContainer()
            ->getParameter('kernel.project_dir');

        $result = $this->runCommand(
            FrontendJsRoutingDumpCommand::getDefaultName(),
            ['-vvv', sprintf('--target=%s%s', $projectDir, $this->getEndPath('custom_routes', 'json'))]
        );

        $this->assertNotEmpty($result);
        self::assertStringContainsString($this->getEndPath('frontend_custom_routes', 'json'), $result);
    }

    private function getEndPath(string $filename, string $format): string
    {
        return implode(DIRECTORY_SEPARATOR, ['', 'public', 'media', 'js', $filename . '.' . $format]);
    }
}
