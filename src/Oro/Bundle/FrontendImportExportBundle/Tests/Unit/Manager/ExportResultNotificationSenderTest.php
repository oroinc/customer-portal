<?php

namespace Oro\Bundle\FrontendImportExportBundle\Tests\Unit\Manager;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\EmailBundle\Entity\EmailUser;
use Oro\Bundle\EmailBundle\Model\From;
use Oro\Bundle\EmailBundle\Tools\AggregatedEmailTemplatesSender;
use Oro\Bundle\FrontendImportExportBundle\Async\Export\FrontendExportResultSummarizer;
use Oro\Bundle\FrontendImportExportBundle\Entity\FrontendImportExportResult;
use Oro\Bundle\FrontendImportExportBundle\Manager\ExportResultNotificationSender;
use Oro\Bundle\NotificationBundle\Model\NotificationSettings;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;

class ExportResultNotificationSenderTest extends \PHPUnit\Framework\TestCase
{
    private const SENDER_EMAIL = 'John Doe <doe@example.org>';

    /** @var AggregatedEmailTemplatesSender|\PHPUnit\Framework\MockObject\MockObject */
    private $emailTemplatesSender;

    /** @var FrontendExportResultSummarizer|\PHPUnit\Framework\MockObject\MockObject */
    private $exportResultSummarizer;

    /** @var WebsiteManager|\PHPUnit\Framework\MockObject\MockObject */
    private $websiteManager;

    /** @var ExportResultNotificationSender */
    private $sender;

    protected function setUp(): void
    {
        $this->emailTemplatesSender = $this->createMock(AggregatedEmailTemplatesSender::class);
        $this->exportResultSummarizer = $this->createMock(FrontendExportResultSummarizer::class);
        $this->websiteManager = $this->createMock(WebsiteManager::class);

        $notificationSettings = $this->createMock(NotificationSettings::class);
        $notificationSettings->expects(self::any())
            ->method('getSender')
            ->willReturn(From::emailAddress(self::SENDER_EMAIL));

        $this->sender = new ExportResultNotificationSender(
            $this->emailTemplatesSender,
            $this->exportResultSummarizer,
            $notificationSettings,
            $this->websiteManager
        );
    }

    public function testSendEmailNotificationWhenNoCustomerUser(): void
    {
        $this->exportResultSummarizer->expects(self::never())
            ->method('getSummaryFromImportExportResult');

        $this->emailTemplatesSender->expects(self::never())
            ->method('send');

        self::assertEmpty($this->sender->sendEmailNotification(new FrontendImportExportResult()));
    }

    /**
     * @dataProvider sendEmailNotificationDataProvider
     */
    public function testSendEmailNotification(array $exportResultSummary, string $templateName): void
    {
        $website = new Website();
        $customerUser = (new CustomerUser())->setWebsite($website);
        $importExportResult = (new FrontendImportExportResult())->setCustomerUser($customerUser);

        $this->exportResultSummarizer->expects(self::once())
            ->method('getSummaryFromImportExportResult')
            ->with($importExportResult)
            ->willReturn($exportResultSummary);

        $currentWebsite = new Website();
        $this->websiteManager->expects(self::once())
            ->method('getCurrentWebsite')
            ->willReturn($currentWebsite);

        $this->websiteManager->expects(self::exactly(2))
            ->method('setCurrentWebsite')
            ->withConsecutive([$website], [$currentWebsite]);

        $emailUser = $this->createMock(EmailUser::class);
        $this->emailTemplatesSender->expects(self::once())
            ->method('send')
            ->with(
                $importExportResult,
                [$customerUser],
                From::emailAddress(self::SENDER_EMAIL),
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
