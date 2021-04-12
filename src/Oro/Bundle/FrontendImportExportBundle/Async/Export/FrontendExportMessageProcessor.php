<?php

namespace Oro\Bundle\FrontendImportExportBundle\Async\Export;

use Oro\Bundle\FrontendImportExportBundle\Async\Topics;
use Oro\Bundle\ImportExportBundle\Async\Export\ExportMessageProcessor;
use Oro\Bundle\ImportExportBundle\File\FileManager;

/**
 * Process export running on frontend. Uses the same export logic as basic processor but with specific for frontend
 * export handler.
 */
class FrontendExportMessageProcessor extends ExportMessageProcessor
{
    /**
     * @param FileManager $fileManager
     */
    public function setFileManager(FileManager $fileManager): void
    {
        $this->fileManager = $fileManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedTopics(): array
    {
        return [Topics::EXPORT];
    }
}
