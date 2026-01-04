<?php

namespace Oro\Bundle\CustomerBundle\Mailer;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Event\CustomerUserEmailSendEvent;
use Oro\Bundle\UserBundle\Mailer\UserTemplateEmailSender;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Handles CustomerUser email sending logic
 */
class Processor
{
    public const WELCOME_EMAIL_TEMPLATE_NAME = 'customer_user_welcome_email';
    public const DUPLICATE_EMAIL_TEMPLATE_NAME = 'not_unique_customer_user_email';
    public const WELCOME_EMAIL_REGISTERED_BY_ADMIN_TEMPLATE_NAME = 'customer_user_welcome_email_registered_by_admin';
    public const CONFIRMATION_EMAIL_TEMPLATE_NAME = 'customer_user_confirmation_email';
    public const RESET_PASSWORD_EMAIL_TEMPLATE_NAME = 'customer_user_reset_password';

    /**
     * @var UserTemplateEmailSender
     */
    private $userTemplateEmailSender;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(
        UserTemplateEmailSender $userTemplateEmailSender,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->userTemplateEmailSender = $userTemplateEmailSender;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function sendDuplicateEmailNotification(CustomerUser $customerUser): int
    {
        return $this->getEmailTemplateAndSendEmail(
            $customerUser,
            static::DUPLICATE_EMAIL_TEMPLATE_NAME,
            ['entity' => $customerUser]
        );
    }

    /**
     * @param CustomerUser $customerUser
     * @return int
     */
    public function sendWelcomeNotification(CustomerUser $customerUser)
    {
        return $this->getEmailTemplateAndSendEmail(
            $customerUser,
            static::WELCOME_EMAIL_TEMPLATE_NAME,
            ['entity' => $customerUser]
        );
    }

    /**
     * @param CustomerUser $customerUser
     * @return int
     */
    public function sendWelcomeForRegisteredByAdminNotification(CustomerUser $customerUser)
    {
        return $this->getEmailTemplateAndSendEmail(
            $customerUser,
            static::WELCOME_EMAIL_REGISTERED_BY_ADMIN_TEMPLATE_NAME,
            ['entity' => $customerUser]
        );
    }

    /**
     * @param CustomerUser $customerUser
     * @return int
     */
    public function sendConfirmationEmail(CustomerUser $customerUser)
    {
        return $this->getEmailTemplateAndSendEmail(
            $customerUser,
            static::CONFIRMATION_EMAIL_TEMPLATE_NAME,
            ['entity' => $customerUser, 'token' => $customerUser->getConfirmationToken()]
        );
    }

    /**
     * @param CustomerUser $customerUser
     * @return int
     */
    public function sendResetPasswordEmail(CustomerUser $customerUser)
    {
        return $this->getEmailTemplateAndSendEmail(
            $customerUser,
            static::RESET_PASSWORD_EMAIL_TEMPLATE_NAME,
            ['entity' => $customerUser]
        );
    }

    private function getEmailTemplateAndSendEmail(
        CustomerUser $user,
        $emailTemplateName,
        array $emailTemplateParams
    ): int {
        $event = new CustomerUserEmailSendEvent($user, $emailTemplateName, $emailTemplateParams, $user->getWebsite());
        $this->eventDispatcher->dispatch($event, CustomerUserEmailSendEvent::NAME);

        return $this->userTemplateEmailSender->sendUserTemplateEmail(
            $user,
            $event->getEmailTemplate(),
            $event->getEmailTemplateParams(),
            $event->getScope()
        );
    }
}
