<?php
declare(strict_types=1);

namespace Oro\Bundle\FrontendImportExportBundle\Command\Cron;

use Gaufrette\File;
use Oro\Bundle\FrontendImportExportBundle\Manager\FrontendImportExportResultManager;
use Oro\Bundle\ImportExportBundle\Command\Cron\CleanupStorageCommandAbstract;
use Oro\Bundle\ImportExportBundle\File\FileManager;
use Symfony\Component\Console\Input\InputOption;

/**
 * Deletes front store old temporary import/export files.
 */
class FrontendExportCleanupStorageCommand extends CleanupStorageCommandAbstract
{
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

    public function getDefaultDefinition()
    {
        return '0 0 */1 * *';
    }

    /** @noinspection PhpMissingParentCallCommonInspection */
    protected function configure()
    {
        $this
            ->addOption(
                'interval',
                'i',
                InputOption::VALUE_OPTIONAL,
                'Time interval (days) to keep the front store import and export files.'.
                ' Will be removed files older than today-interval.',
                static::DEFAULT_PERIOD
            )
            ->setDescription('Deletes old store front import/export files.')
            ->setHelp(
                <<<'HELP'
The <info>%command.name%</info> command deletes old store front import/export files.
  <info>php %command.full_name%</info>
The <info>--interval</info> option can be used to override the default time period (14 days)
past which the temporary import files are considered old:
  <info>php %command.full_name% --interval=<days></info>
HELP
            )
            ->addUsage('--interval=<days>')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function getFilesForDeletion($from, $to): array
    {
        $this->importExportResultManager->markResultsAsExpired($from, $to);
        return $this->fileManager->getFilesByPeriod($from, $to);
    }

    /**
     * {@inheritdoc}
     */
    protected function deleteFile(File $file): void
    {
        $this->fileManager->deleteFile($file);
    }
}
