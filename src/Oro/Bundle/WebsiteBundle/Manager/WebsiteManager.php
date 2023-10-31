<?php

namespace Oro\Bundle\WebsiteBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\MaintenanceBundle\Maintenance\MaintenanceModeState;
use Oro\Bundle\MaintenanceBundle\Maintenance\MaintenanceRestrictionsChecker;
use Oro\Bundle\WebsiteBundle\Entity\Website;

/**
 * Provides methods to manage the current website.
 */
class WebsiteManager
{
    private ManagerRegistry $doctrine;
    private FrontendHelper $frontendHelper;
    private MaintenanceModeState $maintenance;
    private MaintenanceRestrictionsChecker $maintenanceRestrictionsChecker;
    private ?Website $currentWebsite = null;

    public function __construct(
        ManagerRegistry $doctrine,
        FrontendHelper $frontendHelper,
        MaintenanceModeState $maintenance,
        MaintenanceRestrictionsChecker $maintenanceRestrictionsChecker
    ) {
        $this->doctrine = $doctrine;
        $this->frontendHelper = $frontendHelper;
        $this->maintenance = $maintenance;
        $this->maintenanceRestrictionsChecker = $maintenanceRestrictionsChecker;
    }

    public function getCurrentWebsite(): ?Website
    {
        if (null !== $this->currentWebsite) {
            return $this->currentWebsite;
        }

        if (!$this->frontendHelper->isFrontendRequest()) {
            return null;
        }

        if ($this->maintenance->isOn() && !$this->maintenanceRestrictionsChecker->isAllowed()) {
            return null;
        }

        $this->currentWebsite = $this->findCurrentWebsite();

        return $this->currentWebsite;
    }

    public function setCurrentWebsite(?Website $currentWebsite): void
    {
        $this->currentWebsite = $currentWebsite;
    }

    public function getDefaultWebsite(): ?Website
    {
        return $this->getEntityManager()
            ->getRepository(Website::class)
            ->getDefaultWebsite();
    }
    /**
     * Method should be called to reset internal memory cache of this manager.
     */
    public function onClear(): void
    {
        $this->currentWebsite = null;
    }

    protected function findCurrentWebsite(): ?Website
    {
        return $this->getDefaultWebsite();
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->doctrine->getManagerForClass(Website::class);
    }
}
