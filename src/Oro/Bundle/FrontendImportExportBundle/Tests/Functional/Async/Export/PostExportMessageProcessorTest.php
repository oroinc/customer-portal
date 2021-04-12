<?php

namespace Oro\Bundle\FrontendImportExportBundle\Tests\Functional\Async\Export;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\FrontendImportExportBundle\Async\Topics;
use Oro\Bundle\FrontendImportExportBundle\Handler\FrontendExportHandler;
use Oro\Bundle\FrontendTestFrameworkBundle\Migrations\Data\ORM\LoadCustomerUserData;
use Oro\Bundle\ImportExportBundle\Exception\RuntimeException;
use Oro\Bundle\MessageQueueBundle\Test\Functional\MessageQueueExtension;
use Oro\Bundle\NotificationBundle\Async\Topics as NotificationTopics;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\WebsiteSearchBundle\Tests\Functional\Traits\DefaultWebsiteIdTestTrait;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Job\JobProcessor;
use Oro\Component\MessageQueue\Transport\Message;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\MessageQueue\Util\JSON;

/**
 * @dbIsolationPerTest
 */
class PostExportMessageProcessorTest extends WebTestCase
{
    use MessageQueueExtension;
    use DefaultWebsiteIdTestTrait;

    private FrontendExportHandler $exportHandler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->initClient();
        $this->exportHandler = $this->createMock(FrontendExportHandler::class);
        $this->getContainer()->set('oro_frontend_importexport.handler.export_handler.stub', $this->exportHandler);
    }

    public function testProcessWithValidData(): void
    {
        // Set website to current authorized user.
        $user = $this->getCurrentUser();
        $website = $this->getDefaultWebsite();
        $user->setWebsite($website);

        $em = $this->getContainer()->get('doctrine')->getManagerForClass(CustomerUser::class);
        $em->persist($user);
        $em->flush();

        $rootJob = $this->getJobProcessor()->findOrCreateRootJob(
            'test_export_message',
            'oro:export:test_export_message'
        );

        $childJob = $this->getJobProcessor()->findOrCreateChildJob(
            'oro:export:test_export_message:chunk.1',
            $rootJob
        );

        $childJob->setData([
            'file' => 'export1.csv',
            'success' => true,
            'entities' => 'acme'
        ]);

        $messageData = [
            'jobId' => $rootJob->getId(),
            'jobName' => 'oro:export:test_export_message',
            'exportType' => 'csv',
            'outputFormat' => 'csv',
            'email' => 'test@test.com',
            'entity' => 'ACME',
            'userId' => 1,
            'refererUrl' => '/products'
        ];

        $message = new Message();
        $message->setMessageId('test_import_message');
        $message->setBody(JSON::encode($messageData));

        $this->exportHandler->expects($this->once())
            ->method('exportResultFileMerge')
            ->willReturn('export.csv');

        $processor = $this->getContainer()->get('oro_frontend_importexport.async.processor.post_export');

        $result = $processor->process($message, $this->createSessionMock());

        $this->assertMessageSent(
            Topics::SAVE_IMPORT_EXPORT_RESULT,
            [
                'jobId' => $rootJob->getId(),
                'type' => 'csv',
                'entity' => 'ACME',
            ]
        );

        $sender = $this->getContainer()->get('oro_notification.model.notification_settings')
            ->getSender()->toArray();

        $this->assertMessageSent(
            NotificationTopics::SEND_NOTIFICATION_EMAIL,
            [
                'sender' => $sender,
                'toEmail' => 'test@test.com',
                'body' => [
                    'exportResult' => [
                        'entities' => 'acme',
                        'success' => true,
                        'fileName' => 'export.csv',
                        'url' => 'http://localhost/export/download/' . $rootJob->getId(),
                        'user' => 'CustomerUser CustomerUser',
                        'tryAgainUrl' => 'http://localhost/products'
                    ],
                    'jobName' => 'oro:export:test_export_message',
                ],
                'contentType' => 'text/html',
                'template' => 'frontend_export_result'
            ]
        );

        $this->assertEquals(MessageProcessorInterface::ACK, $result);
    }

    public function testProcessWithInValidData(): void
    {
        $rootJob = $this->getJobProcessor()->findOrCreateRootJob(
            'test_export_message',
            'oro:export:test_export_message'
        );

        $childJob = $this->getJobProcessor()->findOrCreateChildJob(
            'oro:export:test_export_message:chunk.1',
            $rootJob
        );

        $childJob->setData([
            'file' => 'export1.csv',
            'success' => true,
            'entities' => 'acme'
        ]);

        $messageData = [
            'jobId' => $rootJob->getId(),
            'jobName' => 'oro:export:test_export_message',
            'exportType' => 'csv',
            'outputFormat' => 'csv',
            'email' => 'test@test.com',
            'entity' => 'ACME',
            'userId' => 1,
            'refererUrl' => '/products'
        ];

        $message = new Message();
        $message->setMessageId('test_import_message');
        $message->setBody(JSON::encode($messageData));

        $this->exportHandler->expects($this->once())
            ->method('exportResultFileMerge')
            ->willThrowException(new RuntimeException());

        $processor = $this->getContainer()->get('oro_frontend_importexport.async.processor.post_export');

        $result = $processor->process($message, $this->createSessionMock());

        $this->assertMessagesEmpty(Topics::SAVE_IMPORT_EXPORT_RESULT);
        $this->assertMessagesEmpty(NotificationTopics::SEND_NOTIFICATION_EMAIL);
        $this->assertEquals(MessageProcessorInterface::ACK, $result);
    }

    public function testProcessWithMessageWithoutUserId(): void
    {
        $rootJob = $this->getJobProcessor()->findOrCreateRootJob(
            'test_export_message',
            'oro:export:test_export_message'
        );

        $childJob = $this->getJobProcessor()->findOrCreateChildJob(
            'oro:export:test_export_message:chunk.1',
            $rootJob
        );

        $childJob->setData([
            'file' => 'export1.csv',
            'success' => true,
            'entities' => 'acme'
        ]);

        $messageData = [
            'jobId' => $rootJob->getId(),
            'jobName' => 'oro:export:test_export_message',
            'exportType' => 'csv',
            'outputFormat' => 'csv',
            'email' => 'test@test.com',
            'entity' => 'ACME',
            'refererUrl' => '/products'
        ];

        $message = new Message();
        $message->setMessageId('test_import_message');
        $message->setBody(JSON::encode($messageData));

        $this->exportHandler->expects($this->once())
            ->method('exportResultFileMerge')
            ->willReturn('export.csv');

        $processor = $this->getContainer()->get('oro_frontend_importexport.async.processor.post_export');

        $result = $processor->process($message, $this->createSessionMock());

        $this->assertMessageSent(
            Topics::SAVE_IMPORT_EXPORT_RESULT,
            [
                'jobId' => $rootJob->getId(),
                'type' => 'csv',
                'entity' => 'ACME',
            ]
        );

        $this->assertMessagesEmpty(NotificationTopics::SEND_NOTIFICATION_EMAIL);
        $this->assertEquals(MessageProcessorInterface::ACK, $result);
    }

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

    private function getCurrentUser(): CustomerUser
    {
        return $this->getContainer()->get('doctrine')
            ->getRepository(CustomerUser::class)
            ->findOneBy(['username' => LoadCustomerUserData::AUTH_USER]);
    }
}
