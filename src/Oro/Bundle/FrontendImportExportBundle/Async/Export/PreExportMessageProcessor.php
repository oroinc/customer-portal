<?php

namespace Oro\Bundle\FrontendImportExportBundle\Async\Export;

use Oro\Bundle\FrontendImportExportBundle\Async\Topic\ExportTopic;
use Oro\Bundle\FrontendImportExportBundle\Async\Topic\PostExportTopic;
use Oro\Bundle\FrontendImportExportBundle\Async\Topic\PreExportTopic;
use Oro\Bundle\ImportExportBundle\Async\Export\PreExportMessageProcessor as BasePreExportMessageProcessor;
use Oro\Bundle\ImportExportBundle\Handler\ExportHandler;
use Oro\Component\MessageQueue\Client\Message;
use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Component\MessageQueue\Job\Job;
use Oro\Component\MessageQueue\Job\JobRunner;

/**
 * Class responsible for running the main export job.
 */
class PreExportMessageProcessor extends BasePreExportMessageProcessor
{
    public function setExportHandler(ExportHandler $exportHandler): void
    {
        $this->exportHandler = $exportHandler;
    }

    public static function getSubscribedTopics(): array
    {
        return [PreExportTopic::getName()];
    }

    protected function addDependentJob(Job $rootJob, array $body)
    {
        $context = $this->dependentJob->createDependentJobContext($rootJob);
        $user = $this->getUser();

        $context->addDependentJob(PostExportTopic::getName(), [
            'jobId' => $rootJob->getId(),
            'customerUserId' => $user->getId(),
            'jobName' => $body['jobName'],
            'exportType' => $body['exportType'],
            'outputFormat' => $body['outputFormat'],
            'entity' => $body['entity'],
            'refererUrl' => $body['refererUrl'] ?? null,
        ]);

        $this->dependentJob->saveDependentJob($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function getDelayedJobCallback(array $body, array $ids = [])
    {
        if (!empty($ids)) {
            $body['options']['ids'] = $ids;
        }

        return function (JobRunner $jobRunner, Job $child) use ($body) {
            $body['jobId'] = $child->getId();
            $this->producer->send(
                ExportTopic::getName(),
                new Message($body, MessagePriority::LOW)
            );
        };
    }
}
