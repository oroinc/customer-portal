<?php

namespace Oro\Bundle\FrontendImportExportBundle\Tests\Functional\Async;

use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserData;
use Oro\Bundle\EmailBundle\Entity\EmailUser;
use Oro\Bundle\FrontendImportExportBundle\Entity\FrontendImportExportResult;
use Oro\Bundle\FrontendImportExportBundle\Manager\ExportResultNotificationSender;
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
class SaveFrontendExportResultProcessorTest extends WebTestCase
{
    /** @var ExportResultNotificationSender|\PHPUnit\Framework\MockObject\MockObject */
    private ExportResultNotificationSender $notificationSender;

    protected function setUp(): void
    {
        $this->initClient();

        $this->loadFixtures([
            LoadCustomerUserData::class,
        ]);

        $this->notificationSender = $this->createMock(ExportResultNotificationSender::class);
        self::getContainer()->set(
            'oro_frontend_importexport.manager.export_result_notification_sender.stub',
            $this->notificationSender
        );
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
            JSON::encode(
                [
                    'jobId' => $rootJob->getId(),
                    'type' => ProcessorRegistry::TYPE_EXPORT,
                    'entity' => FrontendImportExportResult::class,
                    'customerUserId' => $customerUser->getId(),
                ]
            )
        );

        $this->notificationSender
            ->expects(self::once())
            ->method('sendEmailNotification')
            ->willReturnCallback(
                function (FrontendImportExportResult $importExportResult) use ($rootJob) {
                    self::assertEquals($rootJob->getId(), $importExportResult->getJobId());
                    self::assertEquals(ProcessorRegistry::TYPE_EXPORT, $importExportResult->getType());
                    self::assertEquals(FrontendImportExportResult::class, $importExportResult->getEntity());
                }
            )
            ->willReturn([$this->createMock(EmailUser::class)]);

        $processor = self::getContainer()->get('oro_frontend_importexport.async.save_frontend_export_result_processor');
        $result = $processor->process($message, $this->createMock(SessionInterface::class));

        self::assertEquals(ExportMessageProcessor::ACK, $result);
    }

    public function testProcessSaveJobWithInvalidData():void
    {
        $message = new Message();
        $message->setMessageId('abc');
        $message->setBody(JSON::encode([]));

        $processor = self::getContainer()
            ->get('oro_frontend_importexport.async.save_frontend_export_result_processor');

        $processorResult = $processor->process($message, $this->createMock(SessionInterface::class));

        self::assertEquals(ExportMessageProcessor::REJECT, $processorResult);
    }

    private function getJobProcessor(): JobProcessor
    {
        return self::getContainer()->get('oro_message_queue.job.processor');
    }
}
