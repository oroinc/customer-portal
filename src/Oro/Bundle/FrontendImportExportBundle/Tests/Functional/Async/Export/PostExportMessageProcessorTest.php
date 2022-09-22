<?php

namespace Oro\Bundle\FrontendImportExportBundle\Tests\Functional\Async\Export;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\FrontendImportExportBundle\Async\Topic\SaveExportResultTopic;
use Oro\Bundle\FrontendImportExportBundle\Handler\FrontendExportHandler;
use Oro\Bundle\FrontendTestFrameworkBundle\Migrations\Data\ORM\LoadCustomerUserData;
use Oro\Bundle\ImportExportBundle\Exception\RuntimeException;
use Oro\Bundle\MessageQueueBundle\Test\Functional\MessageQueueExtension;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\WebsiteSearchBundle\Tests\Functional\Traits\DefaultWebsiteIdTestTrait;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Job\JobProcessor;
use Oro\Component\MessageQueue\Transport\Message;
use Oro\Component\MessageQueue\Transport\SessionInterface;

/**
 * @dbIsolationPerTest
 */
class PostExportMessageProcessorTest extends WebTestCase
{
    use MessageQueueExtension;
    use DefaultWebsiteIdTestTrait;

    private FrontendExportHandler|\PHPUnit\Framework\MockObject\MockObject $exportHandler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->initClient();
        $this->exportHandler = $this->createMock(FrontendExportHandler::class);
        self::getContainer()->set('oro_frontend_importexport.handler.export_handler.stub', $this->exportHandler);
    }

    public function testProcessWithValidData(): void
    {
        // Set website to current authorized user.
        $user = $this->getCurrentUser();
        $website = $this->getDefaultWebsite();
        $user->setWebsite($website);

        $em = self::getContainer()->get('doctrine')->getManagerForClass(CustomerUser::class);
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
            'entity' => 'ACME',
            'customerUserId' => 1,
            'refererUrl' => '/products'
        ];

        $message = new Message();
        $message->setMessageId('test_import_message');
        $message->setBody($messageData);

        $this->exportHandler->expects(self::once())
            ->method('exportResultFileMerge')
            ->willReturn('export.csv');

        $processor = self::getContainer()->get('oro_frontend_importexport.async.processor.post_export');

        $result = $processor->process($message, $this->createMock(SessionInterface::class));

        self::assertMessageSent(
            SaveExportResultTopic::getName(),
            [
                'jobId' => $rootJob->getId(),
                'type' => 'csv',
                'entity' => 'ACME',
                'customerUserId' => 1,
            ]
        );

        self::assertEquals(MessageProcessorInterface::ACK, $result);
    }

    public function testProcessWithInvalidData(): void
    {
        $rootJob = $this->getJobProcessor()->findOrCreateRootJob(
            'test_export_message',
            'oro:export:test_export_message'
        );

        $childJob = $this->getJobProcessor()->findOrCreateChildJob(
            'oro:export:test_export_message:chunk.1',
            $rootJob
        );

        $childJob->setData(
            [
                'file' => 'export1.csv',
                'success' => true,
                'entities' => 'acme'
            ]
        );

        $messageData = [
            'jobId' => $rootJob->getId(),
            'jobName' => 'oro:export:test_export_message',
            'exportType' => 'csv',
            'outputFormat' => 'csv',
            'entity' => 'ACME',
            'customerUserId' => 1,
            'refererUrl' => '/products'
        ];

        $message = new Message();
        $message->setMessageId('test_import_message');
        $message->setBody($messageData);

        $this->exportHandler->expects(self::once())
            ->method('exportResultFileMerge')
            ->willThrowException(new RuntimeException());

        $processor = self::getContainer()->get('oro_frontend_importexport.async.processor.post_export');

        $result = $processor->process($message, $this->createMock(SessionInterface::class));

        self::assertMessagesEmpty(SaveExportResultTopic::getName());
        self::assertEquals(MessageProcessorInterface::ACK, $result);
    }

    private function getJobProcessor(): JobProcessor
    {
        return self::getContainer()->get('oro_message_queue.job.processor');
    }

    private function getCurrentUser(): CustomerUser
    {
        return self::getContainer()->get('doctrine')
            ->getRepository(CustomerUser::class)
            ->findOneBy(['username' => LoadCustomerUserData::AUTH_USER]);
    }
}
