<?php

namespace Oro\Bundle\FrontendImportExportBundle\Tests\Unit\Manager;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\EmailBundle\Entity\EmailUser;
use Oro\Bundle\EmailBundle\Tools\AggregatedEmailTemplatesSender;
use Oro\Bundle\FrontendImportExportBundle\Async\Export\FrontendExportResultSummarizer;
use Oro\Bundle\FrontendImportExportBundle\Entity\FrontendImportExportResult;
use Oro\Bundle\FrontendImportExportBundle\Manager\ExportResultNotificationSender;
use Oro\Bundle\NotificationBundle\Model\NotificationSettings;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;

class ExportResultNotificationSenderTest extends \PHPUnit\Framework\TestCase
{
    private const SENDER_EMAIL = 'John Doe <example.org>';

    private AggregatedEmailTemplatesSender|\PHPUnit\Framework\MockObject\MockObject $emailTemplatesSender;

    private FrontendExportResultSummarizer|\PHPUnit\Framework\MockObject\MockObject $exportResultSummarizer;

    private NotificationSettings|\PHPUnit\Framework\MockObject\MockObject $notificationSettings;

    private WebsiteManager|\PHPUnit\Framework\MockObject\MockObject $websiteManager;

    private ExportResultNotificationSender $sender;

    protected function setUp(): void
    {
        $this->emailTemplatesSender = $this->createMock(AggregatedEmailTemplatesSender::class);
        $this->exportResultSummarizer = $this->createMock(FrontendExportResultSummarizer::class);
        $this->notificationSettings = $this->createMock(NotificationSettings::class);
        $this->websiteManager = $this->createMock(WebsiteManager::class);

        $this->sender = new ExportResultNotificationSender(
            $this->emailTemplatesSender,
            $this->exportResultSummarizer,
            $this->notificationSettings,
            $this->websiteManager
        );

        $this->notificationSettings
            ->expects(self::any())
            ->method('getSenderEmail')
            ->willReturn(self::SENDER_EMAIL);
    }

    public function testSendEmailNotificationWhenNoCustomerUser(): void
    {
        $this->exportResultSummarizer
            ->expects(self::never())
            ->method('getSummaryFromImportExportResult');

        $this->emailTemplatesSender
            ->expects(self::never())
            ->method('send');

        self::assertEmpty($this->sender->sendEmailNotification(new FrontendImportExportResult()));
    }

    /**
     * @dataProvider sendEmailNotificationDataProvider
     *
     * @param array $exportResultSummary
     * @param string $templateName
     */
    public function testSendEmailNotification(array $exportResultSummary, string $templateName): void
    {
        $website = new Website();
        $customerUser = (new CustomerUser())->setWebsite($website);
        $importExportResult = (new FrontendImportExportResult())->setCustomerUser($customerUser);

        $this->exportResultSummarizer
            ->expects(self::once())
            ->method('getSummaryFromImportExportResult')
            ->with($importExportResult)
            ->willReturn($exportResultSummary);

        $currentWebsite = new Website();
        $this->websiteManager
            ->expects(self::once())
            ->method('getCurrentWebsite')
            ->willReturn($currentWebsite);

        $this->websiteManager
            ->expects(self::exactly(2))
            ->method('setCurrentWebsite')
            ->withConsecutive([$website], [$currentWebsite]);

        $emailUser = $this->createMock(EmailUser::class);
        $this->emailTemplatesSender
            ->expects(self::once())
            ->method('send')
            ->with(
                $importExportResult,
                [$customerUser],
                self::SENDER_EMAIL,
                $templateName,
                $exportResultSummary
            )
            ->willReturn([$emailUser]);

        $result = $this->sender->sendEmailNotification($importExportResult);
        self::assertEquals([$emailUser], $result);
    }

    public function sendEmailNotificationDataProvider(): array
    {
        return [
            [
                '$exportResultSummary' => ['exportResult' => ['success' => true]],
                '$templateName' => FrontendExportResultSummarizer::TEMPLATE_SUCCESS_EXPORT_RESULT,
            ],
            [
                '$exportResultSummary' => ['exportResult' => ['success' => false]],
                '$templateName' => FrontendExportResultSummarizer::TEMPLATE_FAILED_EXPORT_RESULT,
            ],
            [
                '$exportResultSummary' => ['exportResult' => []],
                '$templateName' => FrontendExportResultSummarizer::TEMPLATE_FAILED_EXPORT_RESULT,
            ],
            [
                '$exportResultSummary' => [],
                '$templateName' => FrontendExportResultSummarizer::TEMPLATE_FAILED_EXPORT_RESULT,
            ],
        ];
    }
}
