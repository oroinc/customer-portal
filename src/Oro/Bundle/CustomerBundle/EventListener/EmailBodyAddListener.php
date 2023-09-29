<?php

namespace Oro\Bundle\CustomerBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ActivityBundle\Manager\ActivityManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\EmailBundle\Entity\Email;
use Oro\Bundle\EmailBundle\Event\EmailBodyAdded;

/**
 * Links email entity with customer users which may be found by recipient emails.
 */
class EmailBodyAddListener
{
    private ManagerRegistry $doctrine;
    private ActivityManager $activityManager;

    public function __construct(ManagerRegistry $doctrine, ActivityManager $activityManager)
    {
        $this->doctrine = $doctrine;
        $this->activityManager = $activityManager;
    }

    public function linkToCustomerUser(EmailBodyAdded $event): void
    {
        $email = $event->getEmail();

        $customerUserEmails = $this->getCustomerUserEmails($email);
        if (!$customerUserEmails) {
            return;
        }

        /** @var EntityManagerInterface $em */
        $em = $this->doctrine->getManagerForClass(CustomerUser::class);

        $users = $em->getRepository(CustomerUser::class)->findBy(['email' => $customerUserEmails]);
        if (!$users) {
            return;
        }

        $this->activityManager->addActivityTargets($email, $users);
        $em->flush();
    }

    private function getCustomerUserEmails(Email $email): array
    {
        $emails = [];
        foreach ($email->getRecipients() as $recipient) {
            $address = $recipient->getEmailAddress();
            if ($address) {
                $emails[] = $address->getEmail();
            }
        }

        return $emails;
    }
}
