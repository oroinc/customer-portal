<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional\Sender;

use Oro\Bundle\ConfigBundle\Tests\Functional\Traits\ConfigManagerAwareTestTrait;
use Oro\Bundle\EmailBundle\Model\EmailTemplateCriteria;
use Oro\Bundle\NotificationBundle\Model\NotificationSettings;
use Oro\Bundle\OrderBundle\Entity\Order;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadUser;
use Psr\Log\Test\TestLogger;
use Symfony\Bundle\FrameworkBundle\Test\MailerAssertionsTrait;

/**
 * Adds layout theme-related cases in addition to the original ones
 * in {@see \Oro\Bundle\EmailBundle\Tests\Functional\Sender\EmailTemplateSenderTest}
 *
 * @dbIsolationPerTest
 */
class EmailTemplateSenderTest extends WebTestCase
{
    use MailerAssertionsTrait;
    use ConfigManagerAwareTestTrait;

    protected function setUp(): void
    {
        $this->initClient();

        $this->loadFixtures([
            '@OroFrontendBundle/Tests/Functional/Sender/DataFixtures/EmailTemplateSender.yml',
        ]);
    }

    protected function tearDown(): void
    {
        $configManager = self::getConfigManager();
        $configManager->set('oro_frontend.frontend_theme', 'default');
        $configManager->flush();
    }

    public function testWhenRegularEmailTemplate(): void
    {
        $logger = new TestLogger();

        $emailTemplateSender = self::getContainer()->get('oro_email.sender.email_template_sender');
        $emailTemplateSender->setLogger($logger);

        /** @var NotificationSettings $notificationSettings */
        $notificationSettings = self::getContainer()->get('oro_notification.model.notification_settings');
        $user = $this->getReference(LoadUser::USER);
        $order = $this->getReference('order1');

        $emailTemplateSender->sendEmailTemplate(
            $notificationSettings->getSender(),
            $user,
            'email_template_order_regular_default',
            ['entity' => $order]
        );

        self::assertFalse($logger->hasErrorRecords(), 'Got records: ' . json_encode($logger->records));
        self::assertEmailCount(1);
        $mailerMessage = self::getMailerMessage();
        self::assertEmailSubjectContains($mailerMessage, 'Email Template Order Regular');
        self::assertEmailHtmlBodyContains($mailerMessage, 'Email Template Order Regular Content');
    }

    public function testWhenEmailTemplateExtended(): void
    {
        $logger = new TestLogger();

        $emailTemplateSender = self::getContainer()->get('oro_email.sender.email_template_sender');
        $emailTemplateSender->setLogger($logger);

        /** @var NotificationSettings $notificationSettings */
        $notificationSettings = self::getContainer()->get('oro_notification.model.notification_settings');
        $user = $this->getReference(LoadUser::USER);
        $order = $this->getReference('order1');

        $emailTemplateSender->sendEmailTemplate(
            $notificationSettings->getSender(),
            $user,
            new EmailTemplateCriteria('email_template_order_extended_default', Order::class),
            ['entity' => $order]
        );

        self::assertFalse($logger->hasErrorRecords(), 'Got records: ' . json_encode($logger->records));
        self::assertEmailCount(1);
        $mailerMessage = self::getMailerMessage();
        self::assertEmailSubjectContains($mailerMessage, 'Email Template Order Extended');
        self::assertEmailHtmlBodyContains($mailerMessage, 'Email Template Order Base Content');
        self::assertEmailHtmlBodyContains($mailerMessage, 'Email Template Order Extended Content');
    }

    public function testWhenEmailTemplateInDbIsExtended(): void
    {
        $logger = new TestLogger();

        $emailTemplateSender = self::getContainer()->get('oro_email.sender.email_template_sender');
        $emailTemplateSender->setLogger($logger);

        /** @var NotificationSettings $notificationSettings */
        $notificationSettings = self::getContainer()->get('oro_notification.model.notification_settings');
        $user = $this->getReference(LoadUser::USER);
        $order = $this->getReference('order1');

        $emailTemplateSender->sendEmailTemplate(
            $notificationSettings->getSender(),
            $user,
            new EmailTemplateCriteria('email_template_order_extended', Order::class),
            ['entity' => $order]
        );

        self::assertFalse($logger->hasErrorRecords(), 'Got records: ' . json_encode($logger->records));
        self::assertEmailCount(1);
        $mailerMessage = self::getMailerMessage();
        self::assertEmailSubjectContains($mailerMessage, 'Email Template Order in Db Extended');
        self::assertEmailHtmlBodyContains($mailerMessage, 'Email Template Order Base Content');
        self::assertEmailHtmlBodyContains($mailerMessage, 'Email Template Order in Db Extended Content');
    }

    public function testWhenEmailTemplateInDbIsExtendedAndLocalized(): void
    {
        $logger = new TestLogger();

        $emailTemplateSender = self::getContainer()->get('oro_email.sender.email_template_sender');
        $emailTemplateSender->setLogger($logger);

        /** @var NotificationSettings $notificationSettings */
        $notificationSettings = self::getContainer()->get('oro_notification.model.notification_settings');
        $user = $this->getReference(LoadUser::USER);
        $order = $this->getReference('order1');
        $localizationFr = $this->getReference('localization_fr');

        $emailTemplateSender->sendEmailTemplate(
            $notificationSettings->getSender(),
            $user,
            new EmailTemplateCriteria('email_template_order_extended', Order::class),
            ['entity' => $order],
            ['localization' => $localizationFr]
        );

        self::assertFalse($logger->hasErrorRecords(), 'Got records: ' . json_encode($logger->records));
        self::assertEmailCount(1);
        $mailerMessage = self::getMailerMessage();
        self::assertEmailSubjectContains($mailerMessage, 'Email Template Order in Db (FR) Extended');
        self::assertEmailHtmlBodyContains($mailerMessage, 'Email Template Order Base Content');
        self::assertEmailHtmlBodyContains($mailerMessage, 'Email Template Order in Db (FR) Extended Content');
    }
}
