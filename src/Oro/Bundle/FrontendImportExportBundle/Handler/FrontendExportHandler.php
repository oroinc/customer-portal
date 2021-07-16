<?php

namespace Oro\Bundle\FrontendImportExportBundle\Handler;

use Oro\Bundle\ImportExportBundle\File\FileManager;
use Oro\Bundle\ImportExportBundle\Handler\ExportHandler;

/**
 * Handles export from frontend. Executes the same logic as base handler but with specific for frontend export file
 * manager.
 */
class FrontendExportHandler extends ExportHandler
{
    public function setFileManager(FileManager $fileManager): void
    {
        $this->fileManager = $fileManager;
    }
}
