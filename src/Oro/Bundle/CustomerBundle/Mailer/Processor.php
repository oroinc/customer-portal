<?php

namespace Oro\Bundle\CustomerBundle\Mailer;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;

class Processor extends CustomerUserProcessor
{
    const WELCOME_EMAIL_TEMPLATE_NAME = 'customer_user_welcome_email';
    const WELCOME_EMAIL_REGISTERED_BY_ADMIN_TEMPLATE_NAME = 'customer_user_welcome_email_registered_by_admin';
    const CONFIRMATION_EMAIL_TEMPLATE_NAME = 'customer_user_confirmation_email';
    const RESET_PASSWORD_EMAIL_TEMPLATE_NAME = 'customer_user_reset_password';

    /**
     * @param CustomerUser $customerUser
     * @return int
     */
    public function sendWelcomeForRegisteredByAdminNotification(CustomerUser $customerUser)
    {
        $emailTemplate = $this->findEmailTemplateByName(static::WELCOME_EMAIL_REGISTERED_BY_ADMIN_TEMPLATE_NAME);
        if ($emailTemplate) {
            return $this->getEmailTemplateAndSendEmail(
                $customerUser,
                static::WELCOME_EMAIL_REGISTERED_BY_ADMIN_TEMPLATE_NAME,
                ['entity' => $customerUser]
            );
        }

        return $this->sendWelcomeNotification($customerUser, null);
    }

    /**
     * @param CustomerUser $customerUser
     * @param string $password
     * @return int
     */
    public function sendWelcomeNotification(CustomerUser $customerUser, $password)
    {
        return $this->getEmailTemplateAndSendEmail(
            $customerUser,
            static::WELCOME_EMAIL_TEMPLATE_NAME,
            ['entity' => $customerUser, 'password' => $password]
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
}
