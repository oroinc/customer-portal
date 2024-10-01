<?php

namespace Oro\Bundle\WebsiteBundle\Provider;

use Oro\Bundle\ScopeBundle\Manager\ScopeCriteriaProviderInterface;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;

/**
 * The scope criteria provider for the current website.
 */
class ScopeCriteriaProvider implements ScopeCriteriaProviderInterface
{
    public const WEBSITE = 'website';

    /** @var WebsiteManager */
    private $websiteManager;

    public function __construct(WebsiteManager $websiteManager)
    {
        $this->websiteManager = $websiteManager;
    }

    #[\Override]
    public function getCriteriaField()
    {
        return self::WEBSITE;
    }

    #[\Override]
    public function getCriteriaValue()
    {
        return $this->websiteManager->getCurrentWebsite();
    }

    #[\Override]
    public function getCriteriaValueType()
    {
        return Website::class;
    }
}
