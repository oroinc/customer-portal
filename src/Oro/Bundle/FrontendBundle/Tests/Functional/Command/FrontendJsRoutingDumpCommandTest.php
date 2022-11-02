<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional\Command;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Component\Testing\TempDirExtension;

class FrontendJsRoutingDumpCommandTest extends WebTestCase
{
    use TempDirExtension;

    private const COMMAND_NAME = 'oro:frontend:js-routing:dump';
    private const GAUFRETTE_BASE_PATH = 'gaufrette://public_js/js/';

    private string $tempDir;

    protected function setUp(): void
    {
        $this->initClient();
        $this->tempDir = $this->getTempDir('frontend_js_routing_dump_command');
    }

    private function doTest(string $targetFilePath, array $commandArguments): void
    {
        $backupFilePath = null;
        if (file_exists($targetFilePath)) {
            $backupFilePath = $this->tempDir . DIRECTORY_SEPARATOR . 'routes.bkp';
            $this->moveFile($targetFilePath, $backupFilePath);
        }

        $params = array_map(
            function (string $k, string $v): string {
                return sprintf('%s=%s', $k, $v);
            },
            array_keys($commandArguments),
            array_values($commandArguments)
        );
        try {
            $result = $this->runCommand(self::COMMAND_NAME, $params, true, true);
            self::assertStringContainsString($targetFilePath, $result);
            self::assertFileExists($targetFilePath);
        } finally {
            if (file_exists($targetFilePath)) {
                unlink($targetFilePath);
            }
            if ($backupFilePath) {
                $this->moveFile($backupFilePath, $targetFilePath);
            }
        }
    }

    private function moveFile(string $from, string $to): void
    {
        // the rename() function cannot be used across stream wrappers
        file_put_contents($to, file_get_contents($from));
        unlink($from);
    }

    private function reinitialiseClient(): void
    {
        self::resetClient();
        $this->initClient();
    }

    public function testExecute(): void
    {
        $this->doTest(self::GAUFRETTE_BASE_PATH . 'frontend_routes.json', []);
    }

    public function testExecuteWithJsFormat(): void
    {
        $this->doTest(self::GAUFRETTE_BASE_PATH . 'frontend_routes.js', ['--format' => 'js']);
    }

    public function testExecuteWithCustomTarget(): void
    {
        $this->doTest(
            $this->tempDir . DIRECTORY_SEPARATOR . 'frontend_test_routes.json',
            ['--target' => $this->tempDir . DIRECTORY_SEPARATOR . 'test_routes.json']
        );
    }

    public function testExecuteWithCustomTargetWithGaufrettePath(): void
    {
        $this->doTest(
            self::GAUFRETTE_BASE_PATH . 'frontend_test_routes.json',
            ['--target' => self::GAUFRETTE_BASE_PATH . 'test_routes.json']
        );
    }

    public function testExecuteWithCustomTargetWhenFilenameStartsWithBackendPrefix(): void
    {
        $backendFilenamePrefix = self::getContainer()->getParameter('oro_navigation.js_routing_filename_prefix');
        $this->doTest(
            self::GAUFRETTE_BASE_PATH . 'frontend_test_routes.json',
            ['--target' => self::GAUFRETTE_BASE_PATH . $backendFilenamePrefix . 'test_routes.json']
        );
    }

    public function testExecuteWithCustomTargetWhenFilenameStartsWithFrontendPrefix(): void
    {
        $targetFilePath = self::GAUFRETTE_BASE_PATH . 'frontend_test_routes.json';
        $this->doTest($targetFilePath, ['--target' => $targetFilePath]);
    }

    public function testExecuteWithJsFormatAndCustomTarget(): void
    {
        $this->doTest(
            self::GAUFRETTE_BASE_PATH . 'frontend_test_routes.txt',
            ['--format' => 'js', '--target' => self::GAUFRETTE_BASE_PATH . 'test_routes.txt']
        );
    }

    public function testUpdatedDefaultOptions(): void
    {
        // re-initialize the client to be sure that the command definition is not cached
        $this->reinitialiseClient();

        $result = $this->runCommand(self::COMMAND_NAME, ['--help'], true, true);
        self::assertStringContainsString('[default: "json"]', $result);
        self::assertStringContainsString(
            '[default: "' . self::GAUFRETTE_BASE_PATH . 'frontend_routes.json"]',
            $result
        );
    }

    public function testUpdatedDefaultOptionsWhenHelpCommandIsUsed(): void
    {
        // re-initialize the client to be sure that the command definition is not cached
        $this->reinitialiseClient();

        $result = $this->runCommand('help', [self::COMMAND_NAME], true, true);
        self::assertStringContainsString('[default: "json"]', $result);
        self::assertStringContainsString(
            '[default: "' . self::GAUFRETTE_BASE_PATH . 'frontend_routes.json"]',
            $result
        );
    }
}
