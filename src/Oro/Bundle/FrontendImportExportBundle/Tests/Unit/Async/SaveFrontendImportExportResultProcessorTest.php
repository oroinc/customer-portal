<?php

namespace Oro\Bundle\FrontendImportExportBundle\Tests\Unit\Async;

use Oro\Bundle\FrontendImportExportBundle\Async\SaveFrontendImportExportResultProcessor;
use Oro\Bundle\FrontendImportExportBundle\Manager\FrontendImportExportResultManager;
use Oro\Bundle\ImportExportBundle\Processor\ProcessorRegistry;
use Oro\Bundle\MessageQueueBundle\Entity\Repository\JobRepository;
use Oro\Bundle\TestFrameworkBundle\Test\Logger\LoggerAwareTraitTestTrait;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Job\Job;
use Oro\Component\MessageQueue\Job\JobProcessor;
use Oro\Component\MessageQueue\Transport\Message;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\MessageQueue\Util\JSON;

class SaveFrontendImportExportResultProcessorTest extends \PHPUnit\Framework\TestCase
{
    use LoggerAwareTraitTestTrait;

    private SaveFrontendImportExportResultProcessor $saveExportResultProcessor;

    /** @var \PHPUnit\Framework\MockObject\MockObject|FrontendImportExportResultManager */
    private FrontendImportExportResultManager $importExportResultManager;

    /** @var JobProcessor|\PHPUnit\Framework\MockObject\MockObject  */
    private JobProcessor $jobProcessor;

    protected function setUp(): void
    {
        $this->jobRepository = $this->createMock(JobRepository::class);
        $this->importExportResultManager = $this->createMock(FrontendImportExportResultManager::class);
        $this->jobProcessor = $this->createMock(JobProcessor::class);

        $this->saveExportResultProcessor = new SaveFrontendImportExportResultProcessor(
            $this->importExportResultManager,
            $this->jobProcessor
        );

        $this->setUpLoggerMock($this->saveExportResultProcessor);
    }

    public function testProcessWithValidMessage(): void
    {
        $this->assertLoggerNotCalled();
        /** @var SessionInterface|\PHPUnit\Framework\MockObject\MockObject */
        $session = $this->createMock(SessionInterface::class);

        $message = new Message();
        $message->setBody(JSON::encode([
            'jobId' => '1',
            'type' => ProcessorRegistry::TYPE_EXPORT,
            'entity' => 'Acme',
            'options' => ['test1' => 'test2']
        ]));

        $job = new Job();
        $job->setId(1);

        $this->importExportResultManager
            ->expects($this->once())
            ->method('saveResult')
            ->with(1, ProcessorRegistry::TYPE_EXPORT, 'Acme', null, null);

        $this->jobProcessor
            ->expects($this->once())
            ->method('findJobById')
            ->willReturn($job);

        $result = $this->saveExportResultProcessor->process($message, $session);

        $this->assertEquals(MessageProcessorInterface::ACK, $result);
    }

    /**
     * @param array $parameters
     * @param string $expectedError
     * @dataProvider getProcessWithInvalidMessageDataProvider
     */
    public function testProcessWithInvalidMessage(array $parameters, $expectedError): void
    {
        $this->loggerMock
            ->expects($this->once())
            ->method('critical')
            ->with($this->stringContains($expectedError));
        /** @var SessionInterface|\PHPUnit\Framework\MockObject\MockObject */
        $session = $this->createMock(SessionInterface::class);

        $message = new Message();
        $message->setBody(JSON::encode($parameters));

        $job = new Job();
        $job->setId(1);

        $this->importExportResultManager
            ->expects($this->never())
            ->method('saveResult');

        $this->jobProcessor
            ->expects($this->never())
            ->method('findJobById');

        $result = $this->saveExportResultProcessor->process($message, $session);

        $this->assertEquals(MessageProcessorInterface::REJECT, $result);
    }

    public function getProcessWithInvalidMessageDataProvider(): array
    {
        return [
            'without jobId' => [
                'parameters' => [
                    'type' => ProcessorRegistry::TYPE_EXPORT,
                    'entity' => '1',
                    'options' => ['test1' => 'test2']
                ],
                'expectedError' => 'Error occurred during save result: The required option "jobId" is missing.'
            ],
            'without entity' => [
                'parameters' => [
                    'jobId' => 1,
                    'type' => ProcessorRegistry::TYPE_EXPORT,
                    'options' => ['test1' => 'test2']
                ],
                'expectedError' => 'Error occurred during save result: The required option "entity" is missing.'
            ],
            'without type' => [
                'parameters' => [
                    'jobId' => 1,
                    'entity' => '1',
                    'options' => ['test1' => 'test2']
                ],
                'expectedError' => 'Error occurred during save result: The required option "type" is missing.'
            ],
            'invalid processor' => [
                'parameters' => [
                    'jobId' => 1,
                    'type' => 'invalid_type',
                    'entity' => '1',
                    'options' => ['test1' => 'test2']
                ],
                'expectedError' => 'Error occurred during save result: The option "type" with value "invalid_type" is'
                    . ' invalid. Accepted values are: "export".'
            ],
            'options not array' => [
                'parameters' => [
                    'jobId' => 1,
                    'type' => ProcessorRegistry::TYPE_EXPORT,
                    'entity' => '1',
                    'options' => 1
                ],
                'expectedError' => 'is expected to be of type "array", but is of type'
            ]
        ];
    }
}
