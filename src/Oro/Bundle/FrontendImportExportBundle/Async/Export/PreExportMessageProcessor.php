<?php

namespace Oro\Bundle\FrontendImportExportBundle\Async\Export;

use Oro\Bundle\FrontendImportExportBundle\Async\Topics;
use Oro\Bundle\ImportExportBundle\Async\Export\PreExportMessageProcessor as BasePreExportMessageProcessor;
use Oro\Bundle\ImportExportBundle\Async\ImportExportResultSummarizer;
use Oro\Component\MessageQueue\Job\Job;

/**
 * Class responsible for running the main export job.
 */
class PreExportMessageProcessor extends BasePreExportMessageProcessor
{
    /**
     * {@inheritDoc}
     */
    protected function getJobUniqueName(array $body)
    {
        return sprintf('oro_frontend_importexport.pre_export.%s.user_%s', $body['jobName'], $body['customerUserId']);
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

        $context->addDependentJob(Topics::POST_EXPORT, [
            'jobId' => $rootJob->getId(),
            'email' => $this->getUser()->getEmail(),
            'recipientUserId' => $this->getUser()->getId(),
            'jobName' => $body['jobName'],
            'exportType' => $body['exportType'],
            'outputFormat' => $body['outputFormat'],
            'entity' => $body['entity'],
            'notificationTemplate' =>
                $body['notificationTemplate'] ?? ImportExportResultSummarizer::TEMPLATE_EXPORT_RESULT,
        ]);

        $this->dependentJob->saveDependentJob($context);
    }
}
