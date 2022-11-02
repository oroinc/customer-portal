<?php

namespace Oro\Bundle\FrontendImportExportBundle\Tests\Functional\Async\Export;

use Oro\Bundle\FrontendImportExportBundle\Async\Export\FrontendExportMessageProcessor;
use Oro\Bundle\ImportExportBundle\Handler\ExportHandler;
use Oro\Bundle\ImportExportBundle\Processor\ProcessorRegistry;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Job\JobProcessor;
use Oro\Component\MessageQueue\Transport\Message;
use Oro\Component\MessageQueue\Transport\SessionInterface;

/**
 * @dbIsolationPerTest
 */
class FrontendExportMessageProcessorTest extends WebTestCase
{
    private ExportHandler|\PHPUnit\Framework\MockObject\MockObject $exportHandler;

    protected function setUp(): void
    {
        $this->initClient();

        $this->exportHandler = $this->createMock(ExportHandler::class);
        self::getContainer()->set('oro_frontend_importexport.handler.export_handler.stub', $this->exportHandler);
    }

    public function testCouldBeConstructedByContainer(): void
    {
        $instance = self::getContainer()->get('oro_frontend_importexport.async.processor.export');

        self::assertInstanceOf(FrontendExportMessageProcessor::class, $instance);
    }

    /**
     * @dataProvider exportProcessDataProvider
     */
    public function testProcessExport(
        bool $resultSuccess,
        int $readsCount,
        int $errorsCount,
        string $expectedResult
    ): void {
        $rootJob = $this->getJobProcessor()->findOrCreateRootJob(
            'test_export_message',
            'oro:export:oro_test:test_import_message'
        );
        $childJob = $this->getJobProcessor()->findOrCreateChildJob(
            'oro:export:oro_test:test_import_message:chunk.1',
            $rootJob
        );

        $message = new Message();
        $message->setMessageId('abc');
        $message->setBody([
            'jobId' => $childJob->getId(),
            'jobName' => 'job_name',
            'processorAlias' => 'alias',
            'exportType' => ProcessorRegistry::TYPE_EXPORT,
            'outputFormat' => 'csv',
            'outputFilePrefix' => null,
            'options' => [],
        ]);

        $exportResult = [
            'success' => $resultSuccess,
            'url' => 'http://localhost',
            'readsCount' => $readsCount,
            'errorsCount' => $errorsCount,
            'entities' => 'User',
        ];

        $this->exportHandler->expects($this->once())
            ->method('getExportResult')
            ->with(
                $this->equalTo('job_name'),
                $this->equalTo('alias'),
                $this->equalTo(ProcessorRegistry::TYPE_EXPORT),
                $this->equalTo('csv'),
                $this->equalTo(null),
                $this->equalTo([])
            )
            ->willReturn($exportResult);

        $processor = self::getContainer()->get('oro_frontend_importexport.async.processor.export');

        $result = $processor->process($message, $this->createMock(SessionInterface::class));
        self::assertEquals($expectedResult, $result);
        self::assertCount(5, $childJob->getData());
        self::assertEquals($exportResult, $childJob->getData());
    }

    public function exportProcessDataProvider(): array
    {
        return [
            [
                'resultSuccess' => true,
                'readsCount' => 100,
                'errorsCount' => 0,
                'processResult' => MessageProcessorInterface::ACK
            ], [
                'resultSuccess' => true,
                'readsCount' => 0,
                'errorsCount' => 0,
                'processResult' => MessageProcessorInterface::ACK
            ], [
                'resultSuccess' => false,
                'readsCount' => 0,
                'errorsCount' => 5,
                'processResult' => MessageProcessorInterface::REJECT
            ],
        ];
    }

    private function getJobProcessor(): JobProcessor
    {
        return self::getContainer()->get('oro_message_queue.job.processor');
    }
}
