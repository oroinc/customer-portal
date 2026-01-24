<?php

namespace Oro\Bundle\WebsiteBundle\Entity\EntityListener;

use Oro\Bundle\ScopeBundle\Manager\ScopeManager;
use Oro\Bundle\WebsiteBundle\Entity\Website;

/**
 * Handles Doctrine ORM lifecycle events for Website entities.
 *
 * This listener automatically creates a base scope for each new website when it is persisted
 * to the database. The scope is created using the ScopeManager to ensure proper scope
 * configuration and isolation for website-specific settings and data.
 */
class WebsiteEntityListener
{
    /**
     * @var ScopeManager
     */
    private $scopeManager;

    public function __construct(ScopeManager $scopeManager)
    {
        $this->scopeManager = $scopeManager;
    }

    public function prePersist(Website $website)
    {
        $criteria = $this->scopeManager->getCriteria(ScopeManager::BASE_SCOPE, ['website' => $website]);
        $this->scopeManager->createScopeByCriteria($criteria, false);
    }
}
