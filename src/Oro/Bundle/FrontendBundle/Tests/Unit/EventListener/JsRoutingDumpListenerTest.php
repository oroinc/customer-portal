<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\EventListener;

use Oro\Bundle\FrontendBundle\Command\FrontendJsRoutingDumpCommand;
use Oro\Bundle\FrontendBundle\EventListener\JsRoutingDumpListener;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class JsRoutingDumpListenerTest extends \PHPUnit\Framework\TestCase
{
    /** @var JsRoutingDumpListener */
    private $listener;

    protected function setUp(): void
    {
        $this->listener = new JsRoutingDumpListener();
    }

    private function getEvent(string $commandName, Application $app): ConsoleTerminateEvent
    {
        $command = $this->createMock(Command::class);
        $command->expects(self::once())
            ->method('getName')
            ->willReturn($commandName);
        $command->expects(self::any())
            ->method('getApplication')
            ->willReturn($app);

        $input = $this->createMock(InputInterface::class);
        $input->expects(self::never())
            ->method(self::anything());

        $output = $this->createMock(OutputInterface::class);
        $output->expects(self::never())
            ->method(self::anything());

        return new ConsoleTerminateEvent($command, $input, $output, 0);
    }

    public function testAfterConsoleCommandForUnsupportedCommand(): void
    {
        $app = $this->createMock(Application::class);
        $app->expects(self::never())
            ->method(self::anything());

        $this->listener->afterConsoleCommand($this->getEvent('test', $app));
    }

    public function testAfterConsoleCommand(): void
    {
        $app = $this->createMock(Application::class);

        $exitCode = 123;
        $event = $this->getEvent('fos:js-routing:dump', $app);

        $frontendCommand = $this->createMock(FrontendJsRoutingDumpCommand::class);
        $frontendCommand->expects(self::once())
            ->method('run')
            ->with(self::identicalTo($event->getInput()), self::identicalTo($event->getOutput()))
            ->willReturn($exitCode);

        $app->expects(self::once())
            ->method('find')
            ->with('oro:frontend:js-routing:dump')
            ->willReturn($frontendCommand);

        $this->listener->afterConsoleCommand($event);

        $this->assertEquals($exitCode, $event->getExitCode());
    }
}
