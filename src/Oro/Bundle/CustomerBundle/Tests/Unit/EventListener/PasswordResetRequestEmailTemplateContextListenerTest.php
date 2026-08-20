<?php

declare(strict_types=1);

namespace Oro\Bundle\CustomerBundle\Tests\Unit\EventListener;

use Oro\Bundle\CustomerBundle\Async\PasswordResetRequestContext;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\EventListener\PasswordResetRequestEmailTemplateContextListener;
use Oro\Bundle\EmailBundle\Event\EmailTemplateContextCollectEvent;
use Oro\Bundle\EmailBundle\Model\EmailTemplateCriteria;
use Oro\Bundle\EmailBundle\Model\From;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use PHPUnit\Framework\TestCase;

class PasswordResetRequestEmailTemplateContextListenerTest extends TestCase
{
    private PasswordResetRequestContext $passwordResetRequestContext;
    private PasswordResetRequestEmailTemplateContextListener $listener;

    #[\Override]
    protected function setUp(): void
    {
        $this->passwordResetRequestContext = new PasswordResetRequestContext();
        $this->listener = new PasswordResetRequestEmailTemplateContextListener($this->passwordResetRequestContext);
    }

    public function testOnContextCollectWhenNoLocalizationInContext(): void
    {
        $event = $this->createEvent();

        $this->listener->onContextCollect($event);

        self::assertNull($event->getTemplateContextParameter('localization'));
    }

    public function testOnContextCollectSetsLocalizationOfPasswordResetRequest(): void
    {
        $localization = new Localization();
        $this->passwordResetRequestContext->setLocalization($localization);

        $event = $this->createEvent();

        $this->listener->onContextCollect($event);

        self::assertSame($localization, $event->getTemplateContextParameter('localization'));
    }

    public function testOnContextCollectWhenLocalizationIsAlreadySet(): void
    {
        $this->passwordResetRequestContext->setLocalization(new Localization());

        $alreadySetLocalization = new Localization();
        $event = $this->createEvent();
        $event->setTemplateContextParameter('localization', $alreadySetLocalization);

        $this->listener->onContextCollect($event);

        self::assertSame($alreadySetLocalization, $event->getTemplateContextParameter('localization'));
    }

    private function createEvent(): EmailTemplateContextCollectEvent
    {
        return new EmailTemplateContextCollectEvent(
            From::emailAddress('no-reply@example.com'),
            [new CustomerUser()],
            new EmailTemplateCriteria('customer_user_reset_password')
        );
    }
}
