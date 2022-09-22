<?php

namespace Oro\Bundle\FrontendImportExportBundle\Tests\Unit\Async\Export;

use Oro\Bundle\FrontendImportExportBundle\Async\Export\PostExportMessageProcessor;
use Oro\Bundle\FrontendImportExportBundle\Async\Topic\SaveExportResultTopic;
use Oro\Bundle\ImportExportBundle\Exception\RuntimeException;
use Oro\Bundle\ImportExportBundle\Handler\ExportHandler;
use Oro\Bundle\MessageQueueBundle\Entity\Job;
use Oro\Bundle\MessageQueueBundle\Test\Unit\MessageQueueAssertTrait;
use Oro\Bundle\TestFrameworkBundle\Test\Logger\LoggerAwareTraitTestTrait;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Job\JobManagerInterface;
use Oro\Component\MessageQueue\Job\JobProcessor;
use Oro\Component\MessageQueue\Transport\Message as TransportMessage;
use Oro\Component\MessageQueue\Transport\SessionInterface;

class PostExportMessageProcessorTest extends \PHPUnit\Framework\TestCase
{
    use LoggerAwareTraitTestTrait;
    use MessageQueueAssertTrait;

    private ExportHandler|\PHPUnit\Framework\MockObject\MockObject $exportHandler;

    private JobProcessor|\PHPUnit\Framework\MockObject\MockObject $jobProcessor;

    private JobManagerInterface|\PHPUnit\Framework\MockObject\MockObject $jobManager;

    private PostExportMessageProcessor $postExportMessageProcessor;

    protected function setUp(): void
    {
        $this->exportHandler = $this->createMock(ExportHandler::class);
        $this->jobProcessor = $this->createMock(JobProcessor::class);
        $this->jobManager = $this->createMock(JobManagerInterface::class);

        $this->postExportMessageProcessor = new PostExportMessageProcessor(
            $this->exportHandler,
            self::getMessageProducer(),
            $this->jobProcessor,
            $this->jobManager
        );

        $this->setUpLoggerMock($this->postExportMessageProcessor);
    }

    public function testProcessJobNotFound(): void
    {
        $message = new TransportMessage();
        $message->setBody([
            'jobId' => '1',
            'jobName' => 'job-name',
            'exportType' => 'type',
            'outputFormat' => 'csv',
            'customerUserId' => 1,
            'entity' => 'Acme',
        ]);

        $this->jobProcessor->expects(self::once())
            ->method('findJobById')
            ->with(1)
            ->willReturn(null);

        $this->exportHandler->expects(self::never())
            ->method('exportResultFileMerge')
            ->withAnyParameters();

        $this->jobManager->expects(self::never())
            ->method('saveJob')
            ->withAnyParameters();

        $this->loggerMock->expects(self::once())
            ->method('error')
            ->with('Job not found');

        $result = $this->postExportMessageProcessor->process($message, $this->createMock(SessionInterface::class));

        self::assertSame(MessageProcessorInterface::REJECT, $result);
        self::assertMessagesEmpty(SaveExportResultTopic::getName());
    }

    public function testProcessExceptionsAreHandledDuringMerge(): void
    {
        $message = new TransportMessage();
        $message->setBody([
            'jobId' => '1',
            'jobName' => 'job-name',
            'exportType' => 'type',
            'outputFormat' => 'csv',
            'customerUserId' => 1,
            'entity' => 'Acme',
        ]);

        $this->jobProcessor->expects(self::once())
            ->method('findJobById')
            ->with(1)
            ->willReturn(new Job());

        $this->exportHandler->expects(self::once())
            ->method('exportResultFileMerge')
            ->with('job-name', 'type', 'csv', [])
            ->willReturn('acme_filename');

        $exceptionMessage = 'Exception message';
        $exception = new RuntimeException($exceptionMessage);
        $this->exportHandler->expects(self::once())
            ->method('exportResultFileMerge')
            ->willThrowException($exception);

        $this->loggerMock->expects(self::once())
            ->method('error')
            ->with(
                sprintf('Error occurred during export merge: %s', $exceptionMessage),
                ['exception' => $exception]
            );

        $this->jobManager->expects(self::never())
            ->method('saveJob')
            ->withAnyParameters();

        $result = $this->postExportMessageProcessor->process($message, $this->createMock(SessionInterface::class));

        self::assertSame(MessageProcessorInterface::ACK, $result);
        self::assertMessagesEmpty(SaveExportResultTopic::getName());
    }

    public function testProcess(): void
    {
        $message = new TransportMessage();
        $message->setBody([
            'jobId' => '1',
            'jobName' => 'job-name',
            'exportType' => 'type',
            'outputFormat' => 'csv',
            'customerUserId' => 1,
            'entity' => 'Acme',
            'refererUrl' => '/some/url',
        ]);

        $rootJob = new Job();
        $rootJob->setId(1);
        $childJob = new Job();
        $childJob->setRootJob($rootJob);
        $childJob->setData(['file' => 'file.csv']);
        $rootJob->setChildJobs([$childJob]);

        $this->jobProcessor->expects(self::once())
            ->method('findJobById')
            ->with(1)
            ->willReturn($rootJob);

        $fileName = 'filename.csv';
        $this->exportHandler->expects(self::once())
            ->method('exportResultFileMerge')
            ->with('job-name', 'type', 'csv', ['file.csv'])
            ->willReturn($fileName);

        $rootJob->setData(['file' => $fileName, 'refererUrl' => null]);
        $this->jobManager->expects(self::once())
            ->method('saveJob')
            ->with($rootJob);

        self::assertSame(
            MessageProcessorInterface::ACK,
            $this->postExportMessageProcessor->process($message, $this->createMock(SessionInterface::class))
        );
        self::assertMessageSent(
            SaveExportResultTopic::getName(),
            [
                'jobId' => $rootJob->getId(),
                'type' => 'type',
                'entity' => 'Acme',
                'customerUserId' => 1,
            ],
        );
    }
}
