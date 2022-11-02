<?php

namespace Oro\Bundle\FrontendBundle\EventListener;

use Symfony\Component\Console\Event\ConsoleTerminateEvent;

/**
 * Executes the dumping of exposed storefront routes right after the dumping of exposed backend routes.
 */
class JsRoutingDumpListener
{
    public function afterConsoleCommand(ConsoleTerminateEvent $event): void
    {
        $command = $event->getCommand();
        if (null === $command || 'fos:js-routing:dump' !== $command->getName()) {
            return;
        }

        $frontendJsRoutingDumpCommand = $command->getApplication()->find('oro:frontend:js-routing:dump');
        $event->setExitCode($frontendJsRoutingDumpCommand->run($event->getInput(), $event->getOutput()));
    }
}
