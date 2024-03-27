<?php

namespace Oro\Bundle\FrontendImportExportBundle\Manager;

use Oro\Bundle\EmailBundle\Entity\EmailUser;
use Oro\Bundle\EmailBundle\Sender\EmailTemplateSender;
use Oro\Bundle\FrontendImportExportBundle\Async\Export\FrontendExportResultSummarizer;
use Oro\Bundle\FrontendImportExportBundle\Entity\FrontendImportExportResult;
use Oro\Bundle\NotificationBundle\Model\NotificationSettings;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;

/**
 * Sends email notification for the storefront export result.
 */
class ExportResultNotificationSender
{
    private EmailTemplateSender $emailTemplateSender;

    private FrontendExportResultSummarizer $exportResultSummarizer;

    private NotificationSettings $notificationSettings;

    private WebsiteManager $websiteManager;

    public function __construct(
        EmailTemplateSender $emailTemplateSender,
        FrontendExportResultSummarizer $exportResultSummarizer,
        NotificationSettings $notificationSettings,
        WebsiteManager $websiteManager
    ) {
        $this->emailTemplateSender = $emailTemplateSender;
        $this->exportResultSummarizer = $exportResultSummarizer;
        $this->notificationSettings = $notificationSettings;
        $this->websiteManager = $websiteManager;
    }

    public function sendEmailNotification(FrontendImportExportResult $importExportResult): ?EmailUser
    {
        $customerUser = $importExportResult->getCustomerUser();
        if (!$customerUser) {
            return null;
        }

        $exportResultSummary = $this->exportResultSummarizer
            ->getSummaryFromImportExportResult($importExportResult);

        $previousWebsite = $this->websiteManager->getCurrentWebsite();
        $this->websiteManager->setCurrentWebsite($customerUser->getWebsite());

        $emailUser = $this->emailTemplateSender->sendEmailTemplate(
            $this->notificationSettings->getSender(),
            $customerUser,
            $this->getNotificationTemplate($exportResultSummary['exportResult'] ?? []),
            ['entity' => $importExportResult] + $exportResultSummary
        );

        $this->websiteManager->setCurrentWebsite($previousWebsite);

        return $emailUser;
    }

    private function getNotificationTemplate(array $exportResultSummary): string
    {
        return !empty($exportResultSummary['success'])
            ? FrontendExportResultSummarizer::TEMPLATE_SUCCESS_EXPORT_RESULT
            : FrontendExportResultSummarizer::TEMPLATE_FAILED_EXPORT_RESULT;
    }
}
