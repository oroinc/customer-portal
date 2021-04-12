<?php

namespace Oro\Bundle\FrontendImportExportBundle\Tests\Functional\Async;

use Oro\Bundle\FrontendImportExportBundle\Entity\FrontendImportExportResult;
use Oro\Bundle\ImportExportBundle\Async\Export\ExportMessageProcessor;
use Oro\Bundle\ImportExportBundle\Processor\ProcessorRegistry;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Component\MessageQueue\Job\JobProcessor;
use Oro\Component\MessageQueue\Transport\Message;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\MessageQueue\Util\JSON;

/**
 * @dbIsolationPerTest
 */
class SaveFrontendImportExportResultProcessorTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient();
    }

    public function testProcessSaveJobWithValidData(): void
    {
        $manager = $this->getContainer()->get('doctrine');
        $importExportResultRepository = $manager->getRepository(FrontendImportExportResult::class);

        $rootJob = $this->getJobProcessor()->findOrCreateRootJob(
            'test_export_result_message',
            'oro:export:test_export_result_message'
        );

        $message = new Message();
        $message->setMessageId('abc');
        $message->setBody(JSON::encode([
            'jobId' => $rootJob->getId(),
            'type' => ProcessorRegistry::TYPE_EXPORT,
            'entity' => FrontendImportExportResult::class
        ]));

        $processor = $this->getContainer()
            ->get('oro_frontend_importexport.async.save_frontend_import_export_result_processor');
        $processorResult = $processor->process($message, $this->createSessionMock());

        /** @var FrontendImportExportResult $rootJobResult */
        $rootJobResult = $importExportResultRepository->findOneBy(['jobId' => $rootJob->getId()]);

        self::assertEquals(ExportMessageProcessor::ACK, $processorResult);
        self::assertEquals($rootJob->getId(), $rootJobResult->getJobId());
        self::assertEquals(ProcessorRegistry::TYPE_EXPORT, $rootJobResult->getType());
        self::assertEquals(FrontendImportExportResult::class, $rootJobResult->getEntity());
    }

    public function testProcessSaveJobWithInvalidData():void
    {
        $message = new Message();
        $message->setMessageId('abc');
        $message->setBody(JSON::encode([]));

        $processor = $this->getContainer()
            ->get('oro_frontend_importexport.async.save_frontend_import_export_result_processor');

        $processorResult = $processor->process($message, $this->createSessionMock());

        self::assertEquals(ExportMessageProcessor::REJECT, $processorResult);
    }

    /**
     * @returnJobProcessor
     */
    private function getJobProcessor(): JobProcessor
    {
        return $this->getContainer()->get('oro_message_queue.job.processor');
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|SessionInterface
     */
    private function createSessionMock()
    {
        return $this->createMock(SessionInterface::class);
    }
}
