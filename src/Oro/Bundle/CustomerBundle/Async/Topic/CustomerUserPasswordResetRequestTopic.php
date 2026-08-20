<?php

declare(strict_types=1);

namespace Oro\Bundle\CustomerBundle\Async\Topic;

use Oro\Bundle\UserBundle\Async\Topic\AbstractPasswordResetRequestTopic;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Processes a forgot password request submitted in the storefront.
 */
class CustomerUserPasswordResetRequestTopic extends AbstractPasswordResetRequestTopic
{
    public const string WEBSITE_ID = 'websiteId';
    public const string LOCALIZATION_ID = 'localizationId';

    #[\Override]
    public static function getName(): string
    {
        return 'oro.customer.customer_user_password_reset_request';
    }

    #[\Override]
    public static function getDescription(): string
    {
        return 'Sends the reset password email if the submitted email belongs to a customer user account.';
    }

    #[\Override]
    public function configureMessageBody(OptionsResolver $resolver): void
    {
        parent::configureMessageBody($resolver);

        $resolver
            ->define(self::WEBSITE_ID)
            ->default(null)
            ->allowedTypes('int', 'null');

        $resolver
            ->define(self::LOCALIZATION_ID)
            ->default(null)
            ->allowedTypes('int', 'null');
    }
}
