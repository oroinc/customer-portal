<?php

namespace Oro\Bundle\CustomerBundle\EventListener;

use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;
use Oro\Bundle\CustomerBundle\Entity\CustomerVisitorManager;

/**
 * Sets the session ID for a new customer visitor before it is saved into the database.
 */
class CustomerVisitorSessionIdListener
{
    public function __construct(
        private readonly CustomerVisitorManager $visitorManager
    ) {
    }

    public function prePersist(CustomerVisitor $visitor): void
    {
        if (!$visitor->getSessionId()) {
            $visitor->setSessionId($this->visitorManager->generateSessionId());
        }
    }
}
