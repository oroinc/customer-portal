<?php

namespace Oro\Bundle\FrontendBundle\EventListener;

use Oro\Bundle\FrontendBundle\Command\FrontendJsRoutingDumpCommand;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;

/**
 * Execute the frontend routing command right after FOS routing command.
 */
class JsRoutingDumpListener
{
    /**
     * @param ConsoleTerminateEvent $event
     */
    public function afterConsoleCommand(ConsoleTerminateEvent $event): void
    {
        $command = $event->getCommand();
        if (!$command || 'fos:js-routing:dump' !== $command->getName()) {
            return;
        }

        $command = $command->getApplication()
            ->find(FrontendJsRoutingDumpCommand::NAME);

        $event->setExitCode($command->run($event->getInput(), $event->getOutput()));
    }
}
