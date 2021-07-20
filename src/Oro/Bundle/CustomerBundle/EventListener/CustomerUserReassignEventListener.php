<?php

namespace Oro\Bundle\CustomerBundle\EventListener;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Handler\CustomerUserReassignUpdaterInterface;

/**
 * Call reassign updaters on customer user's customer update.
 */
class CustomerUserReassignEventListener
{
    /** @var CustomerUserReassignUpdaterInterface */
    private $customerUserReassignUpdater;

    public function __construct(CustomerUserReassignUpdaterInterface $customerUserReassignUpdater)
    {
        $this->customerUserReassignUpdater = $customerUserReassignUpdater;
    }

    public function preUpdate(CustomerUser $customerUser, PreUpdateEventArgs $event)
    {
        if (!$event->hasChangedField('customer')) {
            return;
        }

        $this->customerUserReassignUpdater->update($customerUser);
    }
}
