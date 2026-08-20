<?php

declare(strict_types=1);

namespace Oro\Bundle\CustomerBundle\EventListener;

use Oro\Bundle\CustomerBundle\Async\PasswordResetRequestContext;
use Oro\Bundle\EmailBundle\Event\EmailTemplateContextCollectEvent;

/**
 * Sets the localization of the request a forgot password form was submitted in to an email template criteria context.
 */
class PasswordResetRequestEmailTemplateContextListener
{
    public function __construct(
        private readonly PasswordResetRequestContext $passwordResetRequestContext
    ) {
    }

    public function onContextCollect(EmailTemplateContextCollectEvent $event): void
    {
        if ($event->getTemplateContextParameter('localization') !== null) {
            return;
        }

        $localization = $this->passwordResetRequestContext->getLocalization();
        if ($localization !== null) {
            $event->setTemplateContextParameter('localization', $localization);
        }
    }
}
