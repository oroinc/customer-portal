<?php

namespace Oro\Bundle\WebsiteBundle\Manager;

use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\MaintenanceBundle\Maintenance\MaintenanceModeState;
use Oro\Bundle\WebsiteBundle\Entity\Website;

/**
 * Basic website manager.
 * Provides current website.
 */
class WebsiteManager
{
    /**
     * @var ManagerRegistry
     */
    protected $managerRegistry;

    /**
     * @var FrontendHelper
     */
    protected $frontendHelper;

    /**
     * @var Website
     */
    protected $currentWebsite;

    /**
     * @var MaintenanceModeState
     */
    protected $maintenance;

    public function __construct(
        ManagerRegistry $managerRegistry,
        FrontendHelper $frontendHelper,
        MaintenanceModeState $maintenance
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->frontendHelper = $frontendHelper;
        $this->maintenance = $maintenance;
    }

    /**
     * @return Website|null
     */
    public function getCurrentWebsite()
    {
        if (!$this->currentWebsite && !$this->maintenance->isOn()) {
            $this->currentWebsite = $this->getResolvedWebsite();
        }

        return $this->currentWebsite;
    }

    public function setCurrentWebsite(?Website $currentWebsite): void
    {
        $this->currentWebsite = $currentWebsite;
    }

    /**
     * @return Website
     */
    public function getDefaultWebsite()
    {
        return $this->getEntityManager()
            ->getRepository(Website::class)
            ->getDefaultWebsite();
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->managerRegistry->getManagerForClass(Website::class);
    }

    /**
     * @return Website
     */
    protected function getResolvedWebsite()
    {
        if (!$this->frontendHelper->isFrontendRequest()) {
            return null;
        }

        return $this->getDefaultWebsite();
    }

    /**
     * Method should be called to reset saved website
     */
    public function onClear()
    {
        $this->currentWebsite = null;
    }
}
