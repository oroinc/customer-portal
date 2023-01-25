<?php

namespace Oro\Bundle\CustomerBundle\Handler;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\EmailBundle\Model\EmailTemplateCriteria;
use Oro\Bundle\NotificationBundle\Manager\EmailNotificationManager;
use Oro\Bundle\NotificationBundle\Model\TemplateEmailNotification;
use Oro\Bundle\NotificationBundle\Model\TemplateEmailNotificationInterface;
use Psr\Log\LoggerInterface;

/**
 * Responsible for resetting customer user's password,
 * setting auth status to reset
 * and sending reset token to the customer user.
 */
class ResetPasswordHandler
{
    private const TEMPLATE_NAME = 'customer_user_force_reset_password';

    private EmailNotificationManager $mailManager;
    private CustomerUserManager $customerUserManager;
    private LoggerInterface $logger;

    public function __construct(
        EmailNotificationManager $mailManager,
        CustomerUserManager $customerUserManager,
        LoggerInterface $logger
    ) {
        $this->mailManager = $mailManager;
        $this->customerUserManager = $customerUserManager;
        $this->logger = $logger;
    }

    /**
     * Generates reset password confirmation token, block the user from login and sends notification email.
     * Skips disabled users
     */
    public function resetPasswordAndNotify(CustomerUser $user): bool
    {
        if (!$user->isEnabled()) {
            return false;
        }

        if (null === $user->getConfirmationToken()) {
            $user->setConfirmationToken($user->generateToken());
        }

        $this->customerUserManager->setAuthStatus($user, CustomerUserManager::STATUS_RESET);
        $this->customerUserManager->updateUser($user);

        try {
            $this->mailManager->processSingle($this->getNotification($user), [], $this->logger);
        } catch (\Exception $e) {
            $this->logger->error(
                sprintf('Sending email to %s failed.', $user->getEmail()),
                ['exception' => $e]
            );

            return false;
        }

        return true;
    }

    private function getNotification(CustomerUser $customerUser): TemplateEmailNotificationInterface
    {
        return new TemplateEmailNotification(
            new EmailTemplateCriteria(self::TEMPLATE_NAME, CustomerUser::class),
            [$customerUser],
            $customerUser
        );
    }
}
