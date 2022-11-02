<?php

namespace Oro\Bundle\FrontendImportExportBundle\Tests\Functional\Command\Cron;

use Gaufrette\File;
use Oro\Bundle\FrontendImportExportBundle\Command\Cron\FrontendExportCleanupStorageCommand;
use Oro\Bundle\ImportExportBundle\File\FileManager;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @dbIsolationPerTest
 */
class FrontendExportCleanupStorageCommandTest extends WebTestCase
{
    private FileManager|\PHPUnit\Framework\MockObject\MockObject $fileManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->initClient();

        $this->fileManager = $this->createMock(FileManager::class);

        self::getContainer()->set('oro_frontend_importexport.file.file_manager.stub', $this->fileManager);
    }

    public function testExecuteWithoutExpiredFiles(): void
    {
        $this->fileManager->expects(self::once())
            ->method('getFilesByPeriod')
            ->willReturn([]);

        $result = self::runCommand(FrontendExportCleanupStorageCommand::getDefaultName());

        self::assertEquals('Were removed "0" files.', $result);
    }

    public function testExecuteWithExpiredFiles(): void
    {
        $firstFile = $this->createMock(File::class);
        $secondFile = $this->createMock(File::class);

        $this->fileManager->expects(self::once())
            ->method('getFilesByPeriod')
            ->willReturn(['firstFile' => $firstFile, 'secondFile' => $secondFile]);

        $this->fileManager->expects(self::exactly(2))
            ->method('deleteFile')
            ->withConsecutive([$firstFile], [$secondFile]);

        $result = self::runCommand(FrontendExportCleanupStorageCommand::getDefaultName());

        self::assertEquals('Were removed "2" files.', $result);
    }
}
