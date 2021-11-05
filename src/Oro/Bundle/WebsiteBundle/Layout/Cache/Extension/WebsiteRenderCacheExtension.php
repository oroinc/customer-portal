<?php

namespace Oro\Bundle\WebsiteBundle\Layout\Cache\Extension;

use Oro\Bundle\LayoutBundle\Cache\Extension\RenderCacheExtensionInterface;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;

/**
 * Render cache extension that adds website to varyBy cache metadata.
 */
class WebsiteRenderCacheExtension implements RenderCacheExtensionInterface
{
    /**
     * @var WebsiteManager
     */
    private $websiteManager;

    public function __construct(WebsiteManager $websiteManager)
    {
        $this->websiteManager = $websiteManager;
    }

    /**
     * {@inheritDoc}
     */
    public function alwaysVaryBy(): array
    {
        $website = $this->websiteManager->getCurrentWebsite();

        if ($website) {
            return ['website' => $website->getId()];
        }

        return [];
    }
}
