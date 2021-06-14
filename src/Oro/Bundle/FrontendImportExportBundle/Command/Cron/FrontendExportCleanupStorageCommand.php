<?php
declare(strict_types=1);

namespace Oro\Bundle\FrontendImportExportBundle\Command\Cron;

use Gaufrette\File;
use Oro\Bundle\CronBundle\Command\CronCommandInterface;
use Oro\Bundle\FrontendImportExportBundle\Manager\FrontendImportExportResultManager;
use Oro\Bundle\ImportExportBundle\File\FileManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Deletes storefront old temporary import/export files.
 */
class FrontendExportCleanupStorageCommand extends Command implements CronCommandInterface
{
    private const DEFAULT_PERIOD = 14; // days

    /** @var string */
    protected static $defaultName = 'oro:cron:frontend-importexport:clean-up-storage';

    private FileManager $fileManager;
    private FrontendImportExportResultManager $importExportResultManager;

    public function __construct(FileManager $fileManager, FrontendImportExportResultManager $importExportResultManager)
    {
        $this->fileManager = $fileManager;
        $this->importExportResultManager = $importExportResultManager;

        parent::__construct();
    }

    public function getDefaultDefinition(): string
    {
        return '0 0 */1 * *';
    }

    public function isActive()
    {
        return true;
    }

    /** @noinspection PhpMissingParentCallCommonInspection */
    protected function configure(): void
    {
        $this
            ->addOption(
                'interval',
                'i',
                InputOption::VALUE_OPTIONAL,
                'Time interval (days) to keep the storefront import and export files.'.
                ' Will be removed files older than today-interval.',
                static::DEFAULT_PERIOD
            )
            ->setDescription('Deletes old storefront import/export files.')
            ->setHelp(
                <<<'HELP'
The <info>%command.name%</info> command deletes old storefront import/export files.
  <info>php %command.full_name%</info>
The <info>--interval</info> option can be used to override the default time period (14 days)
past which the temporary import files are considered old:
  <info>php %command.full_name% --interval=<days></info>
HELP
            )
            ->addUsage('--interval=<days>')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $period = (int)$input->getOption('interval');

        $from = new \DateTime('@0');
        $to = new \DateTime();
        $to->modify(sprintf('-%d days', $period));

        $this->importExportResultManager->markResultsAsExpired($from, $to);

        $files = $this->fileManager->getFilesByPeriod($from, $to);
        /** @var File $file*/
        foreach ($files as $fileName => $file) {
            $this->fileManager->deleteFile($file);
            $output->writeln(
                sprintf('<info> File "%s" was removed.</info>', $fileName),
                OutputInterface::VERBOSITY_DEBUG
            );
        }

        $output->writeln(sprintf('<info>Were removed "%s" files.</info>', count($files)));
    }
}
