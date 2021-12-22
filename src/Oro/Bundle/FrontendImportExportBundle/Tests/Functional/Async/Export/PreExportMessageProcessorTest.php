<?php

namespace Oro\Bundle\FrontendImportExportBundle\Tests\Functional\Async\Export;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\FrontendImportExportBundle\Async\Topics;
use Oro\Bundle\FrontendImportExportBundle\Handler\FrontendExportHandler;
use Oro\Bundle\FrontendTestFrameworkBundle\Migrations\Data\ORM\LoadCustomerUserData;
use Oro\Bundle\ImportExportBundle\Processor\ProcessorRegistry;
use Oro\Bundle\MessageQueueBundle\Test\Functional\MessageQueueExtension;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Component\MessageQueue\Client\Message as ClientMessage;
use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Job\JobProcessor;
use Oro\Component\MessageQueue\Transport\Message;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\MessageQueue\Util\JSON;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * @dbIsolationPerTest
 */
class PreExportMessageProcessorTest extends WebTestCase
{
    use MessageQueueExtension;

    /** @var FrontendExportHandler|\PHPUnit\Framework\MockObject\MockObject */
    private $exportHandler;

    protected function setUp(): void
    {
        $this->initClient([], self::generateBasicAuthHeader());
        $this->setSecurityToken();
        $this->exportHandler = $this->createMock(FrontendExportHandler::class);
        self::getContainer()->set('oro_frontend_importexport.handler.export_handler.stub', $this->exportHandler);
    }

    public function testProcess(): void
    {
        $rootJob = $this->getJobProcessor()->findOrCreateRootJob(
            'test_pre_export_message',
            'oro:export:test_pre_export_message'
        );

        $messageData = [
            'jobId' => $rootJob->getId(),
            'jobName' => 'oro:export:test_pre_export_message',
            'processorAlias' => 'test_processor',
            'outputFormat' => 'csv',
            'organizationId' => null,
            'exportType' => ProcessorRegistry::TYPE_EXPORT,
            'options' => [
                'currentLocalizationId' => 1,
                'currentCurrency' => 'USD',
                'filteredResultsGrid' => 'frontend-product-search-grid'
            ],
        ];

        $message = new Message();
        $message->setMessageId('test_export_message');
        $message->setBody(JSON::encode($messageData));

        $this->exportHandler->expects(self::once())
            ->method('getExportingEntityIds')
            ->willReturn([1, 2, 3, 4]);

        $processor = self::getContainer()->get('oro_frontend_importexport.async.processor.pre_export');

        $result = $processor->process($message, $this->createMock(SessionInterface::class));

        $userId = $this->getCurrentUser()->getId();

        $exportJob = $this->getJobProcessor()->findOrCreateRootJob(
            'test_export_message',
            'oro_frontend_importexport.pre_export.oro:export:test_pre_export_message.user_' . $userId
        );

        $childJobName = sprintf(
            'oro_frontend_importexport.pre_export.oro:export:test_pre_export_message.user_%d.chunk.1',
            $userId
        );
        $childJob = $this->getJobProcessor()->findOrCreateChildJob($childJobName, $exportJob);

        $expectedMessage = new ClientMessage([
            'jobName' => 'oro:export:test_pre_export_message',
            'processorAlias' => 'test_processor',
            'outputFormat' => 'csv',
            'organizationId' => null,
            'exportType' => 'export',
            'options' => [
                'currentLocalizationId' => 1,
                'currentCurrency' => 'USD',
                'filteredResultsGrid' => 'frontend-product-search-grid',
                'ids' => [1, 2, 3, 4],
            ],
            'jobId' => $childJob->getId(),
            'entity' => null,
        ], MessagePriority::LOW);

        self::assertMessageSent(Topics::EXPORT, $expectedMessage);

        $dataExportJob = $exportJob->getData();

        // Check POST_EXPORT dependent job scheduled.
        self::assertArrayHasKey('dependentJobs', $dataExportJob);
        $dependentJob = current($dataExportJob['dependentJobs']);
        self::assertArrayHasKey('topic', $dependentJob);
        self::assertEquals(Topics::POST_EXPORT, $dependentJob['topic']);

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

    private function setSecurityToken(): void
    {
        $user = $this->getCurrentUser();
        $token = new UsernamePasswordToken($user, false, 'k', $user->getRoles());
        self::getContainer()->get('security.token_storage')->setToken($token);
    }
}
