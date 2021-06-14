<?php

namespace Oro\Bundle\FrontendImportExportBundle\Async\Export;

use Oro\Bundle\FrontendImportExportBundle\Async\Topics;
use Oro\Bundle\ImportExportBundle\Exception\RuntimeException;
use Oro\Bundle\ImportExportBundle\Handler\ExportHandler;
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
 * Processor handles finalize steps of export process.
 */
class PostExportMessageProcessor implements MessageProcessorInterface, TopicSubscriberInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private ExportHandler $exportHandler;

    private MessageProducerInterface $producer;

    private JobProcessor $jobProcessor;

    private JobManagerInterface $jobManager;

    public function __construct(
        ExportHandler $exportHandler,
        MessageProducerInterface $producer,
        JobProcessor $jobProcessor,
        JobManagerInterface $jobManager
    ) {
        $this->exportHandler = $exportHandler;
        $this->producer = $producer;
        $this->jobProcessor = $jobProcessor;
        $this->jobManager = $jobManager;
        $this->logger = new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function process(MessageInterface $message, SessionInterface $session)
    {
        $body = JSON::decode($message->getBody());

        if (!isset(
            $body['jobId'],
            $body['jobName'],
            $body['exportType'],
            $body['outputFormat'],
            $body['entity'],
            $body['customerUserId']
        )) {
            $this->logger->error('Invalid message');
            return self::REJECT;
        }

        if (!($job = $this->jobProcessor->findJobById((int)$body['jobId']))) {
            $this->logger->error('Job not found');

            return self::REJECT;
        }

        /** @var Job $rootJob */
        $rootJob = $job->isRoot() ? $job : $job->getRootJob();
        $files = [];
        foreach ($rootJob->getChildJobs() as $childJob) {
            if (!empty($childJob->getData()) && ($file = $childJob->getData()['file'])) {
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
            $this->logger->error(
                sprintf('Error occurred during export merge: %s', $e->getMessage()),
                ['exception' => $e]
            );
        }

        if ($fileName !== null) {
            $rootJob->setData(
                array_merge($rootJob->getData(), ['file' => $fileName, 'refererUrl' => $body['refererUrl']])
            );
            $this->jobManager->saveJob($rootJob);

            $this->producer->send(
                Topics::SAVE_EXPORT_RESULT,
                [
                    'jobId' => $rootJob->getId(),
                    'type' => $body['exportType'],
                    'entity' => $body['entity'],
                    'customerUserId' => $body['customerUserId'],
                ]
            );
        }

        return self::ACK;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedTopics(): array
    {
        return [Topics::POST_EXPORT];
    }
}
