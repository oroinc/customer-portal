<?php

namespace Oro\Bundle\WebsiteBundle\Provider;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\WebsiteBundle\Entity\Website;

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

    /**
     * {@inheritdoc}
     */
    public function getWebsites()
    {
        $website = $this->getDefaultWebsite();

        return $website ? [$website->getId() => $website] : [];
    }

    /**
     * {@inheritdoc}
     */
    public function getWebsiteIds()
    {
        $website = $this->getDefaultWebsite();

        return $website ? [$website->getId()] : [];
    }

    /**
     * {@inheritdoc}
     */
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
        return $this->registry->getManagerForClass('OroWebsiteBundle:Website')
            ->getRepository('OroWebsiteBundle:Website')
            ->getDefaultWebsite();
    }
}
