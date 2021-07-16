<?php

namespace Oro\Bundle\WebsiteBundle\EventListener;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Provider\CacheableWebsiteProvider;

/**
 * Clears the cache of the website identifiers provider
 * when a new website is created or existing website is deleted or updated.
 */
class WebsiteListener
{
    /** @var CacheableWebsiteProvider */
    private $cacheableWebsiteProvider;

    public function __construct(CacheableWebsiteProvider $cacheableWebsiteProvider)
    {
        $this->cacheableWebsiteProvider = $cacheableWebsiteProvider;
    }

    public function onFlush(OnFlushEventArgs $args)
    {
        if ($this->hasScheduledWebsites($args->getEntityManager()->getUnitOfWork())) {
            $this->cacheableWebsiteProvider->clearCache();
        }
    }

    /**
     * @param UnitOfWork $uow
     *
     * @return bool
     */
    private function hasScheduledWebsites(UnitOfWork $uow)
    {
        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if ($entity instanceof Website) {
                return true;
            }
        }
        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof Website) {
                return true;
            }
        }
        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            if ($entity instanceof Website) {
                return true;
            }
        }

        return false;
    }
}
