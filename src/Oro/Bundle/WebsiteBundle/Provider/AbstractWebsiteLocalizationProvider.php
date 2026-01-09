<?php

namespace Oro\Bundle\WebsiteBundle\Provider;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\LocaleBundle\DependencyInjection\Configuration;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\LocaleBundle\Manager\LocalizationManager;
use Oro\Bundle\WebsiteBundle\Entity\Repository\WebsiteRepository;
use Oro\Bundle\WebsiteBundle\Entity\Website;

/**
 * Provides common functionality for retrieving website-specific localizations.
 *
 * This base class implements the core logic for fetching localizations associated with websites,
 * including fallback to default website and integration with system configuration.
 * Subclasses must implement the getLocalizations method to define specific localization retrieval strategies
 * based on different business rules or contexts.
 */
abstract class AbstractWebsiteLocalizationProvider
{
    /** @var ConfigManager */
    protected $configManager;

    /** @var LocalizationManager */
    protected $localizationManager;

    /** @var DoctrineHelper */
    private $doctrineHelper;

    /** @var WebsiteRepository */
    private $websiteRepository;

    public function __construct(
        ConfigManager $configManager,
        LocalizationManager $localizationManager,
        DoctrineHelper $doctrineHelper
    ) {
        $this->configManager = $configManager;
        $this->localizationManager = $localizationManager;
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * @return WebsiteRepository
     */
    protected function getWebsiteRepository()
    {
        if (!$this->websiteRepository) {
            $this->websiteRepository = $this->doctrineHelper->getEntityRepositoryForClass(Website::class);
        }

        return $this->websiteRepository;
    }

    /**
     * @return array
     */
    protected function getEnabledLocalizationIds()
    {
        return $this->configManager->get(Configuration::getConfigKeyByName(Configuration::ENABLED_LOCALIZATIONS));
    }

    /**
     * @param Website $website
     * @return Localization[]
     */
    abstract public function getLocalizations(Website $website);

    /**
     * @param int $websiteId
     * @return Localization[]
     */
    public function getLocalizationsByWebsiteId($websiteId = null)
    {
        $website = null;
        if ($websiteId && filter_var($websiteId, FILTER_VALIDATE_INT)) {
            $website = $this->getWebsiteRepository()->find((int)$websiteId);
        }

        if (!$website) {
            $website = $this->getWebsiteRepository()->getDefaultWebsite();
        }

        return $this->getLocalizations($website);
    }
}
