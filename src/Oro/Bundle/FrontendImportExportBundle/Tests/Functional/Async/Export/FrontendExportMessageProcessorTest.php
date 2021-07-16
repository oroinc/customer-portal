<?php

namespace Oro\Bundle\FrontendImportExportBundle\Tests\Functional\Async\Export;

use Oro\Bundle\FrontendImportExportBundle\Async\Export\FrontendExportMessageProcessor;
use Oro\Bundle\ImportExportBundle\Async\Export\ExportMessageProcessor;
use Oro\Bundle\ImportExportBundle\Handler\ExportHandler;
use Oro\Bundle\ImportExportBundle\Processor\ProcessorRegistry;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Component\MessageQueue\Job\JobProcessor;
use Oro\Component\MessageQueue\Transport\Message;
use Oro\Component\MessageQueue\Transport\SessionInterface;

/**
 * @dbIsolationPerTest
 */
class FrontendExportMessageProcessorTest extends WebTestCase
{
    /** @var ExportHandler|\PHPUnit\Framework\MockObject\MockObject */
    private ExportHandler $exportHandler;

    protected function setUp(): void
    {
        $this->initClient();

        $this->exportHandler = $this->createMock(ExportHandler::class);
        $this->getContainer()->set('oro_frontend_importexport.handler.export_handler.stub', $this->exportHandler);
    }

    public function testCouldBeConstructedByContainer(): void
    {
        $instance = $this->getContainer()->get('oro_frontend_importexport.async.processor.export');

        $this->assertInstanceOf(FrontendExportMessageProcessor::class, $instance);
    }

    /**
     * @dataProvider exportProcessDataProvider
     */
    public function testProcessExport(
        bool $resultSuccess,
        int $readsCount,
        int $errorsCount,
        string $expectedResult
    ) {
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
        $message->setBody(json_encode([
            'jobId' => $childJob->getId(),
            'jobName' => 'job_name',
            'processorAlias' => 'alias',
        ]));

        $exportResult = [
            'success' => $resultSuccess,
            'url' => 'http://localhost',
            'readsCount' => $readsCount,
            'errorsCount' => $errorsCount,
            'entities' => 'User',
        ];

        $this->exportHandler
            ->expects($this->once())
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

        $processor = $this->getContainer()->get('oro_frontend_importexport.async.processor.export');

        $result = $processor->process($message, $this->createSessionMock());
        $this->assertEquals($expectedResult, $result);
        $this->assertCount(5, $childJob->getData());
        $this->assertEquals($exportResult, $childJob->getData());
    }

    public function exportProcessDataProvider(): array
    {
        return [
            [
                'resultSuccess' => true,
                'readsCount' => 100,
                'errorsCount' => 0,
                'processResult' => ExportMessageProcessor::ACK
            ], [
                'resultSuccess' => true,
                'readsCount' => 0,
                'errorsCount' => 0,
                'processResult' => ExportMessageProcessor::ACK
            ], [
                'resultSuccess' => false,
                'readsCount' => 0,
                'errorsCount' => 5,
                'processResult' => ExportMessageProcessor::REJECT
            ],
        ];
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|SessionInterface
     */
    private function createSessionMock()
    {
        return $this->createMock(SessionInterface::class);
    }

    private function getJobProcessor(): JobProcessor
    {
        return $this->getContainer()->get('oro_message_queue.job.processor');
    }
}
