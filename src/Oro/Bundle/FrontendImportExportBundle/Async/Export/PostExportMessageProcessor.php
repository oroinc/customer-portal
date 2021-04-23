<?php

namespace Oro\Bundle\FrontendImportExportBundle\Async\Export;

use Oro\Bundle\FrontendImportExportBundle\Async\Topics;
use Oro\Bundle\ImportExportBundle\Exception\RuntimeException;
use Oro\Bundle\ImportExportBundle\Handler\ExportHandler;
use Oro\Bundle\NotificationBundle\Async\Topics as NotificationTopics;
use Oro\Bundle\NotificationBundle\Model\NotificationSettings;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Job\Job;
use Oro\Component\MessageQueue\Job\JobManagerInterface;
use Oro\Component\MessageQueue\Job\JobProcessor;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\MessageQueue\Util\JSON;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

/**
 * Processor handles finalize steps of export process and send email notification upon its completion.
 */
class PostExportMessageProcessor implements MessageProcessorInterface, TopicSubscriberInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private ExportHandler $exportHandler;
    private MessageProducerInterface $producer;
    private JobProcessor $jobProcessor;
    private JobManagerInterface $jobManager;
    private FrontendExportResultSummarizer $exportResultSummarizer;
    private NotificationSettings $notificationSettings;

    /**
     * @param ExportHandler $exportHandler
     * @param MessageProducerInterface $producer
     * @param JobProcessor $jobProcessor
     * @param JobManagerInterface $jobManager
     * @param FrontendExportResultSummarizer $exportResultSummarizer
     * @param NotificationSettings $notificationSettings
     */
    public function __construct(
        ExportHandler $exportHandler,
        MessageProducerInterface $producer,
        JobProcessor $jobProcessor,
        JobManagerInterface $jobManager,
        FrontendExportResultSummarizer $exportResultSummarizer,
        NotificationSettings $notificationSettings
    ) {
        $this->exportHandler = $exportHandler;
        $this->producer = $producer;
        $this->jobProcessor = $jobProcessor;
        $this->jobManager = $jobManager;
        $this->exportResultSummarizer = $exportResultSummarizer;
        $this->notificationSettings = $notificationSettings;
        $this->logger = new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function process(MessageInterface $message, SessionInterface $session)
    {
        $body = JSON::decode($message->getBody());

        if (! isset(
            $body['jobId'],
            $body['jobName'],
            $body['exportType'],
            $body['outputFormat'],
            $body['email'],
            $body['entity'],
            $body['userId']
        )) {
            $this->logger->critical('Invalid message');
        }

        if (!($job = $this->jobProcessor->findJobById((int)$body['jobId']))) {
            $this->logger->critical('Job not found');

            return self::REJECT;
        }

        $job = $job->isRoot() ? $job : $job->getRootJob();
        $files = [];

        foreach ($job->getChildJobs() as $childJob) {
            if (! empty($childJob->getData()) && ($file = $childJob->getData()['file'])) {
                $files[] = $file;
            }
        }

        $fileName = null;
        try {
            $fileName = $this->exportHandler->exportResultFileMerge(
                $body['jobName'],
                $body['exportType'],
                $body['outputFormat'],
                $files
            );
        } catch (RuntimeException $e) {
            $this->logger->critical(
                sprintf('Error occurred during export merge: %s', $e->getMessage()),
                ['exception' => $e]
            );
        }

        if ($fileName !== null) {
            $job->setData(array_merge($job->getData(), ['file' => $fileName]));
            $this->jobManager->saveJob($job);

            $this->prepareAndSendEmailNotification($job, $body, $fileName);

            $this->producer->send(
                Topics::SAVE_IMPORT_EXPORT_RESULT,
                [
                    'jobId' => $job->getId(),
                    'type' => $body['exportType'],
                    'entity' => $body['entity'],
                ]
            );
        }

        return self::ACK;
    }

    /**
     * @param string $toEmail
     * @param array $summary
     * @param string|null $notificationTemplate
     * @throws \Oro\Component\MessageQueue\Transport\Exception\Exception
     */
    private function sendEmailNotification(
        string $toEmail,
        array $summary,
        string $notificationTemplate
    ): void {
        $sender = $this->notificationSettings->getSender();

        $message = [
            'sender' => $sender->toArray(),
            'toEmail' => $toEmail,
            'body' => $summary,
            'contentType' => 'text/html',
            'template' => $notificationTemplate,
        ];

        $this->producer->send(
            NotificationTopics::SEND_NOTIFICATION_EMAIL,
            $message
        );

        $this->logger->info('Sent notification email.');
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedTopics(): array
    {
        return [Topics::POST_EXPORT];
    }

    /**
     * @param Job $job
     * @param array $body
     * @param string $filename
     * @throws \Oro\Component\MessageQueue\Transport\Exception\Exception
     */
    private function prepareAndSendEmailNotification(Job $job, array $body, string $filename): void
    {
        $userId = $body['userId'] ?? null;

        if (!$userId) {
            $this->logger->critical('Notification email could not be sent. Parameter "userId" is not defined');
            return;
        }

        $refererUrl = !empty($body['refererUrl']) ? $body['refererUrl'] : null;

        $summary = $this->exportResultSummarizer->processSummaryExportResultForNotification(
            $job,
            $filename,
            $userId,
            $refererUrl
        );

        $notificationTemplate = $this->getNotificationTemplate($body, $summary['exportResult']);

        $this->sendEmailNotification($body['email'], $summary, $notificationTemplate);
    }

    /**
     * @param array $messageData
     * @param array $resultData
     * @return string
     */
    private function getNotificationTemplate(array $messageData, array $resultData): string
    {
        if (isset($messageData['notificationTemplate'])) {
            return $messageData['notificationTemplate'];
        }

        return $resultData['success']
            ? FrontendExportResultSummarizer::TEMPLATE_SUCCESS_EXPORT_RESULT
            : FrontendExportResultSummarizer::TEMPLATE_FAILED_EXPORT_RESULT;
    }
}
