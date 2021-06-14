<?php

namespace Oro\Bundle\FrontendImportExportBundle\Async\Export;

use Oro\Bundle\EntityBundle\Provider\EntityNameResolver;
use Oro\Bundle\FrontendImportExportBundle\Entity\FrontendImportExportResult;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Resolver\WebsiteUrlResolver;
use Oro\Component\MessageQueue\Job\Job;
use Oro\Component\MessageQueue\Job\JobProcessor;

/**
 * Prepares export data by forming a single structure for future use
 */
class FrontendExportResultSummarizer
{
    public const TEMPLATE_SUCCESS_EXPORT_RESULT = 'frontend_export_result_success';
    public const TEMPLATE_FAILED_EXPORT_RESULT = 'frontend_export_result_error';

    private WebsiteUrlResolver $websiteUrlResolver;

    private JobProcessor $jobProcessor;

    private EntityNameResolver $entityNameResolver;

    public function __construct(
        WebsiteUrlResolver $websiteUrlResolver,
        JobProcessor $jobProcessor,
        EntityNameResolver $entityNameResolver
    ) {
        $this->websiteUrlResolver = $websiteUrlResolver;
        $this->jobProcessor = $jobProcessor;
        $this->entityNameResolver = $entityNameResolver;
    }

    public function getSummaryFromImportExportResult(FrontendImportExportResult $importExportResult): array
    {
        $job = $this->jobProcessor->findJobById((int)$importExportResult->getJobId());
        $rootJob = null;
        $rootJobName = '';
        $rootJobId = 0;
        if ($job) {
            $rootJob = $job->isRoot() ? $job : $job->getRootJob();
            $rootJobName = $rootJob->getName();
            $rootJobId = $rootJob->getId();
        }

        $data = $this->getExportResultAsArray($rootJob) + [
                'fileName' => (string) $importExportResult->getFilename(),
                'user' => '',
                'websiteName' => '',
                'tryAgainUrl' => '',
                'url' => '',
            ];

        $customerUser = $importExportResult->getCustomerUser();
        if ($customerUser) {
            $data['user'] = $this->entityNameResolver->getName($customerUser);

            $website = $customerUser->getWebsite();
            if ($website) {
                $data['websiteName'] = $website->getName();
                $data['tryAgainUrl'] = $this->getRefererUrl($data['refererUrl'], $website);
                $data['url'] = $rootJob ? $this->getDownloadUrl($rootJobId, $website) : '';
            }
        }

        return ['exportResult' => $data, 'jobName' => $rootJobName];
    }

    private function getDownloadUrl(int $rootJobId, Website $website): string
    {
        return $this->websiteUrlResolver->getWebsiteSecurePath(
            'oro_frontend_importexport_export_download',
            ['jobId' => $rootJobId],
            $website
        );
    }

    private function getExportResultAsArray(?Job $rootJob): array
    {
        $data = ['entities' => null, 'success' => 0, 'refererUrl' => ''];
        if ($rootJob) {
            $data['refererUrl'] = $rootJob->getData()['refererUrl'] ?? '';

            foreach ($rootJob->getChildJobs() as $childrenJob) {
                $childrenJobData = $childrenJob->getData();
                if (empty($childrenJobData)) {
                    continue;
                }

                $data['success'] += $childrenJobData['success'];
                $data['entities'] = $childrenJobData['entities'] ?? $data['entities'];
            }
        }

        $data['success'] = (bool)$data['success'];

        return $data;
    }

    private function getRefererUrl(string $refererUrl, Website $website): string
    {
        if (!$refererUrl) {
            return $this->websiteUrlResolver->getWebsiteSecurePath('oro_frontend_root', [], $website);
        }

        return sprintf('%s%s', $this->websiteUrlResolver->getWebsiteUrl($website, true), $refererUrl);
    }
}
