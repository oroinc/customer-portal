<?php

namespace Oro\Bundle\FrontendImportExportBundle\Async\Export;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\EntityBundle\Provider\EntityNameResolver;
use Oro\Bundle\ImportExportBundle\Exception\LogicException;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Resolver\WebsiteUrlResolver;
use Oro\Component\MessageQueue\Job\Job;

/**
 * Prepares export data by forming a single structure for future use
 */
class FrontendExportResultSummarizer
{
    public const TEMPLATE_SUCCESS_EXPORT_RESULT = 'frontend_export_result_success';
    public const TEMPLATE_FAILED_EXPORT_RESULT = 'frontend_export_result_error';

    private WebsiteUrlResolver $websiteUrlResolver;
    private ManagerRegistry $managerRegistry;
    private EntityNameResolver $entityNameResolver;

    /**
     * @param WebsiteUrlResolver $websiteUrlResolver
     * @param ManagerRegistry $managerRegistry
     * @param EntityNameResolver $entityNameResolver
     */
    public function __construct(
        WebsiteUrlResolver $websiteUrlResolver,
        ManagerRegistry $managerRegistry,
        EntityNameResolver $entityNameResolver
    ) {
        $this->websiteUrlResolver = $websiteUrlResolver;
        $this->managerRegistry = $managerRegistry;
        $this->entityNameResolver = $entityNameResolver;
    }

    /**
     * @param Job $job
     * @param string $fileName
     * @param int $userId
     * @param string|null $refererUrl
     * @return array
     */
    public function processSummaryExportResultForNotification(
        Job $job,
        string $fileName,
        int $userId,
        ?string $refererUrl = null
    ): array {
        $job = $job->isRoot() ? $job : $job->getRootJob();
        $data = $this->getExportResultAsArray($job);

        $user = $this->getCurrentUser($userId);
        $website = $user->getWebsite();

        $refererUrl = $this->getRefererUrl($refererUrl, $website);

        $data['fileName'] = $fileName;
        $data['user'] = $this->entityNameResolver->getName($user);
        $data['websiteName'] = $website->getName();

        $data['url'] = $this->getDownloadUrl($job->getId(), $website);
        $data['tryAgainUrl'] = $refererUrl;

        return ['exportResult' => $data, 'jobName' => $job->getName()];
    }

    /**
     * @param $jobId
     * @param Website $website
     * @return string
     */
    private function getDownloadUrl($jobId, Website $website): string
    {
        return $this->websiteUrlResolver->getWebsitePath(
            'oro_frontend_importexport_export_download',
            ['jobId' => $jobId],
            $website
        );
    }

    /**
     * @param Job $job
     * @return array
     */
    private function getExportResultAsArray(Job $job): array
    {
        $data = [
            'entities' => null,
            'success'  => 0
        ];

        foreach ($job->getChildJobs() as $childrenJob) {
            $childrenJobData = $childrenJob->getData();
            if (empty($childrenJobData)) {
                continue;
            }

            $data['success']  += $childrenJobData['success'];
            $data['entities'] = $childrenJobData['entities'] ?? $data['entities'];
        }

        $data['success'] = (bool) $data['success'];

        return $data;
    }

    /**
     * @param int $userId
     * @return CustomerUser
     */
    private function getCurrentUser(int $userId): CustomerUser
    {
        $user = $this->managerRegistry->getRepository(CustomerUser::class)
            ->find($userId);

        if (!$user) {
            throw new LogicException(sprintf('Current user with id %d is not found', $userId));
        }

        return $user;
    }

    /**
     * @param string|null $refererUrl
     * @param Website $website
     * @return string
     */
    private function getRefererUrl(?string $refererUrl, Website $website): string
    {
        if (!$refererUrl) {
            return $this->websiteUrlResolver->getWebsiteSecurePath('oro_frontend_root', [], $website);
        }

        return sprintf('%s%s', $this->websiteUrlResolver->getWebsiteUrl($website, true), $refererUrl);
    }
}
