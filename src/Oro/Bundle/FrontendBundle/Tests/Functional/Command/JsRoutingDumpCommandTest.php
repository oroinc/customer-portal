<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional\Command;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class JsRoutingDumpCommandTest extends WebTestCase
{
    /** @var string */
    private $filenamePrefix;

    protected function setUp(): void
    {
        $this->initClient();
        $this->filenamePrefix = $this->getContainer()->getParameter('oro_navigation.js_routing_filename_prefix');

        $this->assertNotEmpty($this->filenamePrefix);
    }

    public function testExecute(): void
    {
        $result = $this->runCommand('fos:js-routing:dump', ['-vvv']);

        $this->assertNotEmpty($result);
        static::assertStringContainsString($this->getEndPath($this->getFilename(), 'json'), $result);
        static::assertStringContainsString($this->getEndPath('frontend_routes', 'json'), $result);
    }

    public function testExecuteWithJsFormat(): void
    {
        $result = $this->runCommand('fos:js-routing:dump', ['-vvv', '--format=js']);

        $this->assertNotEmpty($result);
        static::assertStringContainsString($this->getEndPath($this->getFilename(), 'js'), $result);
        static::assertStringContainsString($this->getEndPath('frontend_routes', 'js'), $result);
    }

    public function testExecuteWithCustomTarget(): void
    {
        $projectDir = $this->getContainer()
            ->getParameter('kernel.project_dir');

        $endPath = $this->getEndPath('custom_routes', 'json');

        $result = $this->runCommand('fos:js-routing:dump', ['-vvv', sprintf('--target=%s%s', $projectDir, $endPath)]);

        $this->assertNotEmpty($result);
        static::assertStringContainsString($endPath, $result);
        static::assertStringContainsString($this->getEndPath('frontend_custom_routes', 'json'), $result);
    }

    private function getEndPath(string $filename, string $format): string
    {
        return implode(DIRECTORY_SEPARATOR, ['', 'public', 'media', 'js', $filename . '.' . $format]);
    }

    private function getFilename(): string
    {
        return $this->filenamePrefix . 'routes';
    }
}
