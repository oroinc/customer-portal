<?php

namespace Oro\Bundle\WebsiteBundle\Provider;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\WebsiteBundle\Entity\Website;

/**
 * Provides information about websites
 */
class WebsiteProvider implements WebsiteProviderInterface
{
    /**
     * @var ManagerRegistry
     */
    protected $registry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    #[\Override]
    public function getWebsites()
    {
        $website = $this->getDefaultWebsite();

        return $website ? [$website->getId() => $website] : [];
    }

    #[\Override]
    public function getWebsiteIds()
    {
        $website = $this->getDefaultWebsite();

        return $website ? [$website->getId()] : [];
    }

    #[\Override]
    public function getWebsiteChoices()
    {
        $website = $this->getDefaultWebsite();

        return $website ? [$website->getName() => $website->getId()] : [];
    }

    /**
     * @return Website|null
     */
    protected function getDefaultWebsite()
    {
        return $this->registry->getManagerForClass(Website::class)
            ->getRepository(Website::class)
            ->getDefaultWebsite();
    }
}
