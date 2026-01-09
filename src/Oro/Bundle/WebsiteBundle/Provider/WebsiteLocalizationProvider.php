<?php

namespace Oro\Bundle\WebsiteBundle\Provider;

use Oro\Bundle\WebsiteBundle\Entity\Website;

/**
 * Provides localizations for a specific website.
 *
 * This provider retrieves the list of enabled localizations for a given website by delegating
 * to the {@see LocalizationManager}. It extends {@see AbstractWebsiteLocalizationProvider} to inherit common
 * localization provider functionality while implementing website-specific localization retrieval.
 */
class WebsiteLocalizationProvider extends AbstractWebsiteLocalizationProvider
{
    #[\Override]
    public function getLocalizations(Website $website)
    {
        return $this->localizationManager->getLocalizations($this->getEnabledLocalizationIds());
    }
}
