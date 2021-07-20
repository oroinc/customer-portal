<?php

namespace Oro\Bundle\CustomerBundle\EventListener;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\EmailBundle\Entity\Email;
use Oro\Bundle\EmailBundle\Entity\Manager\EmailActivityManager;
use Oro\Bundle\EmailBundle\Event\EmailBodyAdded;

/**
 * Links email entity with CustomerUsers which may be found by recipient emails.
 */
class EmailBodyAddListener
{
    /** @var ManagerRegistry */
    private $registry;

    /** @var EmailActivityManager */
    private $activityManager;

    public function __construct(ManagerRegistry $registry, EmailActivityManager $activityManager)
    {
        $this->registry = $registry;
        $this->activityManager = $activityManager;
    }

    public function linkToCustomerUser(EmailBodyAdded $event): void
    {
        $email = $event->getEmail();

        $customerUserEmails = $this->getCustomerUserEmails($email);
        if (!$customerUserEmails) {
            return;
        }

        $manager = $this->registry->getManagerForClass(CustomerUser::class);

        $users = $manager->getRepository(CustomerUser::class)->findBy(['email' => $customerUserEmails]);
        if (!$users) {
            return;
        }

        foreach ($users as $user) {
            $this->activityManager->addAssociation($email, $user);
        }

        $manager->flush();
    }

    private function getCustomerUserEmails(Email $email): array
    {
        $emails = [];
        foreach ($email->getRecipients() as $recipient) {
            $address = $recipient->getEmailAddress();
            if (!$address) {
                continue;
            }

            $emails[] = $address->getEmail();
        }

        return $emails;
    }
}
