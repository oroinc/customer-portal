<?php

namespace Oro\Bundle\FrontendImportExportBundle\Async\Export;

use Oro\Bundle\FrontendImportExportBundle\Async\Topics;
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

    /**
     * {@inheritDoc}
     */
    protected function getJobUniqueName(array $body)
    {
        $userId = $this->getUser()->getId();
        return sprintf('oro_frontend_importexport.pre_export.%s.user_%s', $body['jobName'], $userId);
    }

    public static function getSubscribedTopics(): array
    {
        return [Topics::PRE_EXPORT];
    }

    /**
     * @param Job   $rootJob
     * @param array $body
     */
    protected function addDependentJob(Job $rootJob, array $body)
    {
        $context = $this->dependentJob->createDependentJobContext($rootJob);
        $user = $this->getUser();

        $context->addDependentJob(Topics::POST_EXPORT, [
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
                Topics::EXPORT,
                new Message($body, MessagePriority::LOW)
            );
        };
    }
}
