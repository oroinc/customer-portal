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

    public function testAfterConsoleCommandForUnsupportedCommand(): void
    {
        /** @var Application|\PHPUnit\Framework\MockObject\MockObject $app */
        $app = $this->createMock(Application::class);
        $app->expects($this->never())
            ->method($this->anything());

        $this->listener->afterConsoleCommand($this->getEvent('test', $app));
    }

    public function testAfterConsoleCommand(): void
    {
        /** @var Application|\PHPUnit\Framework\MockObject\MockObject $app */
        $app = $this->createMock(Application::class);

        $event = $this->getEvent('fos:js-routing:dump', $app);

        $fontendCommmand = $this->createMock(FrontendJsRoutingDumpCommand::class);
        $fontendCommmand->expects($this->once())
            ->method('run')
            ->with($event->getInput(), $event->getOutput())
            ->willReturn(123);

        $app->expects($this->once())
            ->method('find')
            ->with('oro:frontend:js-routing:dump')
            ->willReturn($fontendCommmand);

        $this->listener->afterConsoleCommand($event);

        $this->assertEquals(123, $event->getExitCode());
    }

    private function getEvent(string $commandName, Application $app): ConsoleTerminateEvent
    {
        /** @var Command|\PHPUnit\Framework\MockObject\MockObject $command */
        $command = $this->createMock(Command::class);
        $command->expects($this->once())
            ->method('getName')
            ->willReturn($commandName);
        $command->expects($this->any())
            ->method('getApplication')
            ->willReturn($app);

        /** @var InputInterface|\PHPUnit\Framework\MockObject\MockObject $input */
        $input = $this->createMock(InputInterface::class);
        $input->expects($this->never())
            ->method($this->anything());

        /** @var OutputInterface|\PHPUnit\Framework\MockObject\MockObject $output */
        $output = $this->createMock(OutputInterface::class);
        $output->expects($this->never())
            ->method($this->anything());

        return new ConsoleTerminateEvent($command, $input, $output, 0);
    }
}
