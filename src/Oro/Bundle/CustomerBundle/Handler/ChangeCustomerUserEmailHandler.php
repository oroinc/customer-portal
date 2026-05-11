<?php

namespace Oro\Bundle\CustomerBundle\Handler;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\CustomerBundle\Model\EmailHolder;
use Oro\Bundle\EmailBundle\Model\EmailTemplateCriteria;
use Oro\Bundle\NotificationBundle\Manager\EmailNotificationManager;
use Oro\Bundle\NotificationBundle\Model\TemplateEmailNotification;
use Psr\Log\LoggerInterface;

/**
 * Handles customer user email change steps.
 */
class ChangeCustomerUserEmailHandler
{
    public function __construct(
        private EmailNotificationManager $emailNotificationManager,
        private CustomerUserManager $customerUserManager,
        private LoggerInterface $logger
    ) {
    }

    public function initializeEmailChangeAndSendToOldEmail(CustomerUser $customerUser): void
    {
        $customerUser->setNewEmailVerificationCode($customerUser->generateToken());
        $customerUser->setEmailVerificationCodeRequestedAt(new \DateTime('now', new \DateTimeZone('UTC')));
        $this->customerUserManager->updateUser($customerUser);

        $notification = new TemplateEmailNotification(
            new EmailTemplateCriteria('customer_user_email_change_verification_to_old_email', CustomerUser::class),
            [$customerUser],
            $customerUser
        );

        try {
            $this->emailNotificationManager->processSingle($notification, [], $this->logger);
        } catch (\Exception $e) {
            $this->logger->error(
                sprintf('Sending email to %s failed.', $customerUser->getEmail()),
                ['exception' => $e]
            );
        }
    }

    public function sendEmailToNewEmail(CustomerUser $customerUser): bool
    {
        $notification = new TemplateEmailNotification(
            new EmailTemplateCriteria('customer_user_email_change_confirmation', CustomerUser::class),
            [new EmailHolder($customerUser->getNewEmail())],
            $customerUser
        );

        try {
            $this->emailNotificationManager->processSingle($notification, [], $this->logger);
        } catch (\Exception $e) {
            $this->logger->error(
                sprintf('Sending email to %s failed.', $customerUser->getNewEmail()),
                ['exception' => $e]
            );

            return false;
        }

        return true;
    }

    public function confirmNewEmail(CustomerUser $customerUser): void
    {
        $oldEmail = $customerUser->getEmail();

        $customerUser->setEmail($customerUser->getNewEmail());
        $customerUser->setNewEmail(null);
        $customerUser->setEmailVerificationCodeRequestedAt(null);
        $customerUser->setNewEmailVerificationCode(null);
        $this->customerUserManager->updateUser($customerUser);

        $this->sendFinishEmail($customerUser, $oldEmail);
    }

    public function sendFinishEmail(CustomerUser $customerUser, string $oldEmail): void
    {
        $notification = new TemplateEmailNotification(
            new EmailTemplateCriteria('customer_user_email_change_verification_to_new_email', CustomerUser::class),
            [new EmailHolder($oldEmail)],
            $customerUser
        );

        try {
            $this->emailNotificationManager->processSingle($notification, [], $this->logger);
        } catch (\Exception $e) {
            $this->logger->error(
                sprintf('Sending email to %s failed.', $customerUser->getEmail()),
                ['exception' => $e]
            );
        }
    }

    public function cancelEmailChange(CustomerUser $customerUser): void
    {
        $customerUser->setNewEmail(null);
        $customerUser->setEmailVerificationCodeRequestedAt(null);
        $customerUser->setNewEmailVerificationCode(null);
        $this->customerUserManager->updateUser($customerUser);
    }
}
