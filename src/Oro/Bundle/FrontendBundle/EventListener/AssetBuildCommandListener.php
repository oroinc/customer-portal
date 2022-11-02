<?php

namespace Oro\Bundle\FrontendBundle\EventListener;

use Oro\Bundle\AssetBundle\Command\OroAssetsBuildCommand;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * The listener to show warning when using oro:assets:build command at OroCommerce application,
 * as CRM and Platform application build a single theme by default, but for commerce it is highly recommended
 * to work with the single theme for performance reasons.
 */
class AssetBuildCommandListener
{
    public function onConsoleCommand(ConsoleCommandEvent $event)
    {
        $command = $event->getCommand();
        if (!$command || OroAssetsBuildCommand::getDefaultName() !== $command->getName()) {
            return;
        }

        $input = $event->getInput();
        $output = $event->getOutput();
        $io = new SymfonyStyle($input, $output);

        if (!$input->getArgument('theme') && $input->getOption('hot')) {
            $io->note(
                'For performance reasons, it is highly recommended to use the --hot option '.
                "with the <theme> argument. For example: \nbin/console oro:assets:build --hot -- <theme>"
            );
        }
        if (!$input->getArgument('theme') && $input->getOption('watch')) {
            $io->note(
                'For performance reasons, it is highly recommended to use the --watch option '.
                "with the <theme> argument. For example: \nbin/console oro:assets:build --watch -- <theme>"
            );
        }
    }
}
