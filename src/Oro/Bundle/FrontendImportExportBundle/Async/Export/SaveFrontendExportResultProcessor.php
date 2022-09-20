<?php

namespace Oro\Bundle\FrontendImportExportBundle\Async\Export;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\FrontendImportExportBundle\Async\Topic\SaveExportResultTopic;
use Oro\Bundle\FrontendImportExportBundle\Manager\ExportResultNotificationSender;
use Oro\Bundle\FrontendImportExportBundle\Manager\FrontendImportExportResultManager;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Job\JobProcessor;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

/**
 * Processes saving export result data.
 */
class SaveFrontendExportResultProcessor implements
    MessageProcessorInterface,
    TopicSubscriberInterface,
    LoggerAwareInterface
{
    use LoggerAwareTrait;

    private ManagerRegistry $managerRegistry;

    private FrontendImportExportResultManager $importExportResultManager;

    private JobProcessor $jobProcessor;

    private ExportResultNotificationSender $exportResultNotificationSender;

    public function __construct(
        ManagerRegistry $managerRegistry,
        FrontendImportExportResultManager $importExportResultManager,
        JobProcessor $jobProcessor,
        ExportResultNotificationSender $exportResultNotificationSender
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->importExportResultManager = $importExportResultManager;
        $this->jobProcessor = $jobProcessor;
        $this->exportResultNotificationSender = $exportResultNotificationSender;
        $this->logger = new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedTopics(): array
    {
        return [SaveExportResultTopic::getName()];
    }

    /**
     * {@inheritdoc}
     */
    public function process(MessageInterface $message, SessionInterface $session): string
    {
        $messageBody = $message->getBody();

        $job = $this->jobProcessor->findJobById($messageBody['jobId']);
        if (!$job) {
            $this->logger->error(sprintf('Job #%d was not found', $messageBody['jobId']));

            return self::REJECT;
        }

        $customerUser = $this->managerRegistry
            ->getManagerForClass(CustomerUser::class)
            ->find(CustomerUser::class, $messageBody['customerUserId']);
        if ($customerUser === null) {
            $this->logger->error(sprintf('Customer user with id #%d was not found', $messageBody['customerUserId']));

            return self::REJECT;
        }

        $rootJob = $job->isRoot() ? $job : $job->getRootJob();

        try {
            $jobData = $rootJob->getData();
            $frontendImportExportResult = $this->importExportResultManager->saveResult(
                $rootJob->getId(),
                $messageBody['type'],
                $messageBody['entity'],
                $customerUser,
                $jobData['file'] ?? null,
                $messageBody['options']
            );
        } catch (\Exception $e) {
            $this->logger->error(
                sprintf('Unhandled error save result: %s', $e->getMessage()),
                ['exception' => $e]
            );

            return self::REJECT;
        }

        $emailUsers = $this->exportResultNotificationSender->sendEmailNotification($frontendImportExportResult);
        if (!$emailUsers) {
            $this->logger->error(
                sprintf(
                    'Failed to send the export result notification email for customer user with id #%d',
                    $messageBody['customerUserId']
                )
            );

            return self::REJECT;
        }

        return self::ACK;
    }
}
