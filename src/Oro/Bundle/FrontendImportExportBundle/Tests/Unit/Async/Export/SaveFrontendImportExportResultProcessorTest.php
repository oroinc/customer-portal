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

class SaveFrontendImportExportResultProcessorTest extends \PHPUnit\Framework\TestCase
{
    use LoggerAwareTraitTestTrait;

    private FrontendImportExportResultManager|\PHPUnit\Framework\MockObject\MockObject $importExportResultManager;

    private JobProcessor|\PHPUnit\Framework\MockObject\MockObject $jobProcessor;

    private ExportResultNotificationSender|\PHPUnit\Framework\MockObject\MockObject $exportResultNotificationSender;

    private ObjectManager|\PHPUnit\Framework\MockObject\MockObject $entityManager;

    private SaveFrontendExportResultProcessor $saveExportResultProcessor;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(ObjectManager::class);
        $this->importExportResultManager = $this->createMock(FrontendImportExportResultManager::class);
        $this->jobProcessor = $this->createMock(JobProcessor::class);
        $this->exportResultNotificationSender = $this->createMock(ExportResultNotificationSender::class);

        $managerRegistry = $this->createMock(ManagerRegistry::class);
        $managerRegistry->expects(self::any())
            ->method('getManagerForClass')
            ->with(CustomerUser::class)
            ->willReturn($this->entityManager);

        $this->saveExportResultProcessor = new SaveFrontendExportResultProcessor(
            $managerRegistry,
            $this->importExportResultManager,
            $this->jobProcessor,
            $this->exportResultNotificationSender
        );

        $this->setUpLoggerMock($this->saveExportResultProcessor);
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

        $this->jobProcessor->expects(self::once())
            ->method('findJobById')
            ->with($job->getId())
            ->willReturn($job);

        $customerUserId = 42;
        $customerUser = new CustomerUser();
        $this->entityManager->expects(self::once())
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
        $message->setBody($body);

        $importExportResult = $this->createMock(FrontendImportExportResult::class);
        $this->importExportResultManager->expects(self::once())
            ->method('saveResult')
            ->with($rootJob->getId(), $body['type'], $body['entity'], $customerUser, $fileName, $body['options'])
            ->willReturn($importExportResult);

        $emailUser = $this->createMock(EmailUser::class);
        $this->exportResultNotificationSender->expects(self::once())
            ->method('sendEmailNotification')
            ->with($importExportResult)
            ->willReturn([$emailUser]);

        $result = $this->saveExportResultProcessor->process($message, $this->createMock(SessionInterface::class));

        $this->assertLoggerNotCalled();
        self::assertEquals(MessageProcessorInterface::ACK, $result);
    }
}
