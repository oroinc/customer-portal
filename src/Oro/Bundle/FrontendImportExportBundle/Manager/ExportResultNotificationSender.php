<?php

namespace Oro\Bundle\FrontendImportExportBundle\Manager;

use Oro\Bundle\EmailBundle\Entity\EmailUser;
use Oro\Bundle\EmailBundle\Tools\AggregatedEmailTemplatesSender;
use Oro\Bundle\FrontendImportExportBundle\Async\Export\FrontendExportResultSummarizer;
use Oro\Bundle\FrontendImportExportBundle\Entity\FrontendImportExportResult;
use Oro\Bundle\NotificationBundle\Model\NotificationSettings;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;

/**
 * Sends email notification for the storefront export result.
 */
class ExportResultNotificationSender
{
    private AggregatedEmailTemplatesSender $aggregatedEmailTemplatesSender;

    private FrontendExportResultSummarizer $exportResultSummarizer;

    private NotificationSettings $notificationSettings;

    private WebsiteManager $websiteManager;

    public function __construct(
        AggregatedEmailTemplatesSender $aggregatedEmailTemplatesSender,
        FrontendExportResultSummarizer $exportResultSummarizer,
        NotificationSettings $notificationSettings,
        WebsiteManager $websiteManager
    ) {
        $this->aggregatedEmailTemplatesSender = $aggregatedEmailTemplatesSender;
        $this->exportResultSummarizer = $exportResultSummarizer;
        $this->notificationSettings = $notificationSettings;
        $this->websiteManager = $websiteManager;
    }

    /**
     * @param FrontendImportExportResult $importExportResult
     * @return EmailUser[]
     */
    public function sendEmailNotification(FrontendImportExportResult $importExportResult): array
    {
        $customerUser = $importExportResult->getCustomerUser();
        if (!$customerUser) {
            return [];
        }

        $exportResultSummary = $this->exportResultSummarizer
            ->getSummaryFromImportExportResult($importExportResult);

        $previousWebsite = $this->websiteManager->getCurrentWebsite();
        $this->websiteManager->setCurrentWebsite($customerUser->getWebsite());

        $emailUsers = $this->aggregatedEmailTemplatesSender->send(
            $importExportResult,
            [$customerUser],
            $this->notificationSettings->getSender(),
            $this->getNotificationTemplate($exportResultSummary['exportResult'] ?? []),
            $exportResultSummary
        );

        $this->websiteManager->setCurrentWebsite($previousWebsite);

        return $emailUsers;
    }

    private function getNotificationTemplate(array $exportResultSummary): string
    {
        return !empty($exportResultSummary['success'])
            ? FrontendExportResultSummarizer::TEMPLATE_SUCCESS_EXPORT_RESULT
            : FrontendExportResultSummarizer::TEMPLATE_FAILED_EXPORT_RESULT;
    }
}
