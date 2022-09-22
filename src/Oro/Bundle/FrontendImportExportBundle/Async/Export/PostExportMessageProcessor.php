<?php

namespace Oro\Bundle\FrontendImportExportBundle\Async\Export;

use Oro\Bundle\FrontendImportExportBundle\Async\Topic\PostExportTopic;
use Oro\Bundle\FrontendImportExportBundle\Async\Topic\SaveExportResultTopic;
use Oro\Bundle\ImportExportBundle\Exception\RuntimeException;
use Oro\Bundle\ImportExportBundle\Handler\ExportHandler;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Job\JobManagerInterface;
use Oro\Component\MessageQueue\Job\JobProcessor;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
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
        $messageBody = $message->getBody();

        $job = $this->jobProcessor->findJobById($messageBody['jobId']);
        if ($job === null) {
            $this->logger->error('Job not found');

            return self::REJECT;
        }

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
                $messageBody['jobName'],
                $messageBody['exportType'],
                $messageBody['outputFormat'],
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
                array_merge($rootJob->getData(), ['file' => $fileName, 'refererUrl' => $messageBody['refererUrl']])
            );
            $this->jobManager->saveJob($rootJob);

            $this->producer->send(
                SaveExportResultTopic::getName(),
                [
                    'jobId' => $rootJob->getId(),
                    'type' => $messageBody['exportType'],
                    'entity' => $messageBody['entity'],
                    'customerUserId' => $messageBody['customerUserId'],
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
        return [PostExportTopic::getName()];
    }
}
