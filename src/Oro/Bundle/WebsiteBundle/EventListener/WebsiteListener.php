<?php

namespace Oro\Bundle\WebsiteBundle\EventListener;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Provider\CacheableWebsiteProvider;

class WebsiteListener
{
    /** @var CacheableWebsiteProvider */
    private $cacheableWebsiteProvider;

    /**
     * @param CacheableWebsiteProvider $cacheableWebsiteProvider
     */
    public function __construct(CacheableWebsiteProvider $cacheableWebsiteProvider)
    {
        $this->cacheableWebsiteProvider = $cacheableWebsiteProvider;
    }

    /**
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        if ($this->cacheableWebsiteProvider->hasCache() &&
            $this->hasScheduledWebsites($args->getEntityManager()->getUnitOfWork())
        ) {
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
