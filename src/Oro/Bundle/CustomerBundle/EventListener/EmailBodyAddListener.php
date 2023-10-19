<?php

namespace Oro\Bundle\CustomerBundle\EventListener;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\EmailBundle\Entity\Manager\EmailActivityManager;
use Oro\Bundle\EmailBundle\Event\EmailBodyAdded;

/**
 * Links email entity with CustomerUsers which may be found by recipient emails.
 * This listener kept to avoid BC break and will be removed in the next LTS version.
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
    }
}
