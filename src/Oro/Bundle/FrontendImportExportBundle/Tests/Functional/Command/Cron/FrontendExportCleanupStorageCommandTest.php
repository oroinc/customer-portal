<?php

namespace Oro\Bundle\FrontendImportExportBundle\Tests\Functional\Command;

use Gaufrette\File;
use Oro\Bundle\FrontendImportExportBundle\Command\Cron\FrontendExportCleanupStorageCommand;
use Oro\Bundle\ImportExportBundle\File\FileManager;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @dbIsolationPerTest
 */
class FrontendExportCleanupStorageCommandTest extends WebTestCase
{
    /**
     * @var FileManager|\PHPUnit\Framework\MockObject\MockObject
     */
    private FileManager $fileManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->initClient();

        $this->fileManager = $this->createMock(FileManager::class);

        self::getContainer()->set('oro_frontend_importexport.file.file_manager.stub', $this->fileManager);
    }

    public function testExecuteWithoutExpiredFiles(): void
    {
        $this->fileManager
            ->expects($this->once())
            ->method('getFilesByPeriod')
            ->willReturn([]);

        $result = $this->runCommand(FrontendExportCleanupStorageCommand::getDefaultName());

        $this->assertEquals('Were removed "0" files.', $result);
    }

    public function testExecuteWithExpiredFiles(): void
    {
        $firstFile = $this->createMock(File::class);
        $secondFile = $this->createMock(File::class);

        $this->fileManager
            ->expects($this->once())
            ->method('getFilesByPeriod')
            ->willReturn(['firstFile' => $firstFile, 'secondFile' => $secondFile]);

        $this->fileManager
            ->expects($this->exactly(2))
            ->method('deleteFile')
            ->withConsecutive([$firstFile], [$secondFile]);

        $result = $this->runCommand(FrontendExportCleanupStorageCommand::getDefaultName());

        $this->assertEquals('Were removed "2" files.', $result);
    }
}
