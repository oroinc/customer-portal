<?php

namespace Oro\Bundle\CustomerBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Oro\Bundle\CustomerBundle\Acl\Cache\CustomerVisitorAclCache;
use Oro\Bundle\WebsiteBundle\Entity\Website;

/**
 * Clears customer visitor ACL cache if guest role was changed for the website.
 */
class WebsiteUpdateGuestRoleListener
{
    private CustomerVisitorAclCache $visitorAclCache;

    public function __construct(CustomerVisitorAclCache $visitorAclCache)
    {
        $this->visitorAclCache = $visitorAclCache;
    }

    public function postUpdate(Website $entity, LifecycleEventArgs $args)
    {
        foreach ($args->getEntityManager()->getUnitOfWork()->getEntityChangeSet($entity) as $field => $values) {
            if ($field === 'guest_role') {
                if ($values[0] !== $values[1]) {
                    $this->visitorAclCache->clearWebsiteData($entity->getId());
                }
                break;
            }
        }
    }
}
