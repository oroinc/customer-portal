<?php
declare(strict_types=1);

namespace Oro\Bundle\FrontendImportExportBundle\Command\Cron;

use Gaufrette\File;
use Oro\Bundle\FrontendImportExportBundle\Manager\FrontendImportExportResultManager;
use Oro\Bundle\ImportExportBundle\Command\Cron\CleanupStorageCommandAbstract;
use Oro\Bundle\ImportExportBundle\File\FileManager;

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
