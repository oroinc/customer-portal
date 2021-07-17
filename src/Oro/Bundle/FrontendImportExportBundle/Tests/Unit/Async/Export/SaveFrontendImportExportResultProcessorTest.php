<?php

namespace Oro\Bundle\FrontendImportExportBundle\Tests\Unit\Async\Export;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\EmailBundle\Entity\EmailUser;
use Oro\Bundle\FrontendImportExportBundle\Async\Export\SaveFrontendExportResultProcessor;
use Oro\Bundle\FrontendImportExportBundle\Entity\FrontendImportExportResult;
use Oro\Bundle\FrontendImportExportBundle\Manager\ExportResultNotificationSender;
use Oro\Bundle\FrontendImportExportBundle\Manager\FrontendImportExportResultManager;
use Oro\Bundle\ImportExportBundle\Processor\ProcessorRegistry;
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

    /** @var FrontendImportExportResultManager|\PHPUnit\Framework\MockObject\MockObject */
    private FrontendImportExportResultManager $importExportResultManager;

    /** @var JobProcessor|\PHPUnit\Framework\MockObject\MockObject */
    private JobProcessor $jobProcessor;

    /** @var ExportResultNotificationSender|\PHPUnit\Framework\MockObject\MockObject */
    private ExportResultNotificationSender $exportResultNotificationSender;

    private SaveFrontendExportResultProcessor $saveExportResultProcessor;

    /** @var ObjectManager|\PHPUnit\Framework\MockObject\MockObject|\PHPUnit\Framework\MockObject\MockObject */
    private ObjectManager $entityManager;

    protected function setUp(): void
    {
        $managerRegistry = $this->createMock(ManagerRegistry::class);
        $this->importExportResultManager = $this->createMock(FrontendImportExportResultManager::class);
        $this->jobProcessor = $this->createMock(JobProcessor::class);
        $this->exportResultNotificationSender = $this->createMock(ExportResultNotificationSender::class);

        $this->saveExportResultProcessor = new SaveFrontendExportResultProcessor(
            $managerRegistry,
            $this->importExportResultManager,
            $this->jobProcessor,
            $this->exportResultNotificationSender
        );

        $this->setUpLoggerMock($this->saveExportResultProcessor);

        $this->entityManager = $this->createMock(ObjectManager::class);
        $managerRegistry
            ->expects(self::any())
            ->method('getManagerForClass')
            ->with(CustomerUser::class)
            ->willReturn($this->entityManager);
    }

    public function testProcessWithValidMessage(): void
    {
        $rootJob = new Job();
        $fileName = 'sample_file.csv';
        $rootJob->setId(11);
        $rootJob->setData(['file' => $fileName]);

        $job = new Job();
        $job->setId(1);
        $job->setRootJob($rootJob);

        $this->jobProcessor
            ->expects(self::once())
            ->method('findJobById')
            ->with($job->getId())
            ->willReturn($job);

        $customerUserId = 42;
        $customerUser = new CustomerUser();
        $this->entityManager
            ->expects(self::once())
            ->method('find')
            ->with(CustomerUser::class, $customerUserId)
            ->willReturn($customerUser);

        $message = new Message();
        $body = [
            'jobId' => $job->getId(),
            'type' => ProcessorRegistry::TYPE_EXPORT,
            'entity' => \stdClass::class,
            'customerUserId' => $customerUserId,
            'options' => ['sample_key' => 'sample_value'],
        ];
        $message->setBody(JSON::encode($body));

        $importExportResult = $this->createMock(FrontendImportExportResult::class);
        $this->importExportResultManager
            ->expects(self::once())
            ->method('saveResult')
            ->with($rootJob->getId(), $body['type'], $body['entity'], $customerUser, $fileName, $body['options'])
            ->willReturn($importExportResult);

        $emailUser = $this->createMock(EmailUser::class);
        $this->exportResultNotificationSender
            ->expects(self::once())
            ->method('sendEmailNotification')
            ->with($importExportResult)
            ->willReturn([$emailUser]);

        $result = $this->saveExportResultProcessor->process($message, $this->createMock(SessionInterface::class));

        $this->assertLoggerNotCalled();
        self::assertEquals(MessageProcessorInterface::ACK, $result);
    }

    /**
     * @dataProvider getProcessWithInvalidMessageDataProvider
     */
    public function testProcessWithInvalidMessage(array $parameters, string $expectedError): void
    {
        $this->loggerMock
            ->expects(self::once())
            ->method('error')
            ->with(self::stringContains($expectedError));

        $message = new Message();
        $message->setBody(JSON::encode($parameters));

        $job = new Job();
        $job->setId(11);

        $this->jobProcessor
            ->expects(self::any())
            ->method('findJobById')
            ->willReturn($job);

        $this->entityManager
            ->expects(self::any())
            ->method('find')
            ->willReturn(new CustomerUser());

        $this->importExportResultManager
            ->expects(self::never())
            ->method('saveResult');

        $this->exportResultNotificationSender
            ->expects(self::never())
            ->method('sendEmailNotification');

        $result = $this->saveExportResultProcessor->process($message, $this->createMock(SessionInterface::class));

        self::assertEquals(MessageProcessorInterface::REJECT, $result);
    }

    public function getProcessWithInvalidMessageDataProvider(): array
    {
        return [
            'without jobId' => [
                'parameters' => [
                    'type' => ProcessorRegistry::TYPE_EXPORT,
                    'entity' => \stdClass::class,
                    'customerUserId' => 42,
                    'options' => ['test1' => 'test2'],
                ],
                'expectedError' => 'Error occurred during save result: The required option "jobId" is missing.',
            ],
            'jobId not int' => [
                'parameters' => [
                    'jobId' => 'invalid',
                    'type' => ProcessorRegistry::TYPE_EXPORT,
                    'entity' => \stdClass::class,
                    'customerUserId' => 42,
                    'options' => ['test1' => 'test2'],
                ],
                'expectedError' => 'Error occurred during save result: The option "jobId" with value "invalid"'
                    . ' is expected to be of type "int"',
            ],
            'without entity' => [
                'parameters' => [
                    'jobId' => 1,
                    'type' => ProcessorRegistry::TYPE_EXPORT,
                    'customerUserId' => 42,
                    'options' => ['test1' => 'test2'],
                ],
                'expectedError' => 'Error occurred during save result: The required option "entity" is missing.',
            ],
            'without type' => [
                'parameters' => [
                    'jobId' => 1,
                    'entity' => \stdClass::class,
                    'customerUserId' => 42,
                    'options' => ['test1' => 'test2'],
                ],
                'expectedError' => 'Error occurred during save result: The required option "type" is missing.',
            ],
            'invalid processor' => [
                'parameters' => [
                    'jobId' => 1,
                    'type' => 'invalid_type',
                    'entity' => \stdClass::class,
                    'customerUserId' => 42,
                    'options' => ['test1' => 'test2'],
                ],
                'expectedError' => 'Error occurred during save result: The option "type" with value "invalid_type" is'
                    . ' invalid. Accepted values are: "export".',
            ],
            'options not array' => [
                'parameters' => [
                    'jobId' => 1,
                    'type' => ProcessorRegistry::TYPE_EXPORT,
                    'entity' => \stdClass::class,
                    'customerUserId' => 42,
                    'options' => 1,
                ],
                'expectedError' => 'is expected to be of type "array", but is of type',
            ],
            'customerUserId not int' => [
                'parameters' => [
                    'jobId' => 1,
                    'type' => ProcessorRegistry::TYPE_EXPORT,
                    'entity' => \stdClass::class,
                    'customerUserId' => 'invalid',
                    'options' => ['test1' => 'test2'],
                ],
                'expectedError' => 'Error occurred during save result: The option "customerUserId" with value'
                    . ' "invalid" is expected to be of type "int"',
            ],
            'without customerUserId' => [
                'parameters' => [
                    'jobId' => 1,
                    'type' => ProcessorRegistry::TYPE_EXPORT,
                    'entity' => \stdClass::class,
                    'options' => ['test1' => 'test2'],
                ],
                'expectedError' => 'Error occurred during save result: The required option "customerUserId"'
                    . ' is missing.',
            ],
        ];
    }
}
