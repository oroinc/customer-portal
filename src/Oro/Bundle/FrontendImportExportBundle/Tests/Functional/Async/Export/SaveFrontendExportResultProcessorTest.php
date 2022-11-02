<?php

namespace Oro\Bundle\FrontendImportExportBundle\Tests\Functional\Async\Export;

use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserData;
use Oro\Bundle\EmailBundle\Entity\EmailUser;
use Oro\Bundle\FrontendImportExportBundle\Async\Export\SaveFrontendExportResultProcessor;
use Oro\Bundle\FrontendImportExportBundle\Entity\FrontendImportExportResult;
use Oro\Bundle\FrontendImportExportBundle\Manager\ExportResultNotificationSender;
use Oro\Bundle\ImportExportBundle\Processor\ProcessorRegistry;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Job\JobProcessor;
use Oro\Component\MessageQueue\Transport\Message;
use Oro\Component\MessageQueue\Transport\SessionInterface;

/**
 * @dbIsolationPerTest
 */
class SaveFrontendExportResultProcessorTest extends WebTestCase
{
    private ExportResultNotificationSender|\PHPUnit\Framework\MockObject\MockObject $notificationSender;

    private SaveFrontendExportResultProcessor $processor;

    protected function setUp(): void
    {
        $this->initClient();
        $this->loadFixtures([LoadCustomerUserData::class]);

        $this->notificationSender = $this->createMock(ExportResultNotificationSender::class);
        self::getContainer()->set(
            'oro_frontend_importexport.manager.export_result_notification_sender.stub',
            $this->notificationSender
        );

        $this->processor = self::getContainer()
            ->get('oro_frontend_importexport.async.save_frontend_export_result_processor');
    }

    public function testProcessSaveJobWithValidData(): void
    {
        $rootJob = $this->getJobProcessor()->findOrCreateRootJob(
            'test_export_result_message',
            'oro:export:test_export_result_message'
        );

        $customerUser = $this->getReference(LoadCustomerUserData::EMAIL);
        $message = new Message();
        $message->setMessageId('abc');
        $message->setBody(
            [
                'jobId' => $rootJob->getId(),
                'type' => ProcessorRegistry::TYPE_EXPORT,
                'entity' => FrontendImportExportResult::class,
                'customerUserId' => $customerUser->getId(),
                'options' => [],
            ]
        );

        $this->notificationSender->expects(self::once())
            ->method('sendEmailNotification')
            ->willReturnCallback(
                function (FrontendImportExportResult $importExportResult) use ($rootJob) {
                    self::assertEquals($rootJob->getId(), $importExportResult->getJobId());
                    self::assertEquals(ProcessorRegistry::TYPE_EXPORT, $importExportResult->getType());
                    self::assertEquals(FrontendImportExportResult::class, $importExportResult->getEntity());
                }
            )
            ->willReturn([$this->createMock(EmailUser::class)]);

        self::assertEquals(
            MessageProcessorInterface::ACK,
            $this->processor->process($message, $this->createMock(SessionInterface::class))
        );
    }

    public function testProcessSaveJobWithInvalidData():void
    {
        $message = new Message();
        $message->setMessageId('abc');
        $message->setBody([
            'jobId' => PHP_INT_MAX
        ]);

        self::assertEquals(
            MessageProcessorInterface::REJECT,
            $this->processor->process($message, $this->createMock(SessionInterface::class))
        );
    }

    private function getJobProcessor(): JobProcessor
    {
        return self::getContainer()->get('oro_message_queue.job.processor');
    }
}
