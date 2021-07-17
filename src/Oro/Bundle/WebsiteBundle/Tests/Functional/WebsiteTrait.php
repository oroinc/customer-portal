<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Functional;

use Oro\Bundle\WebsiteBundle\Entity\Website;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Simplifies getting website info in tests.
 */
trait WebsiteTrait
{
    /**
     * @return ContainerInterface
     */
    abstract protected static function getContainer();

    protected function getDefaultWebsite(): Website
    {
        return self::getContainer()
            ->get('doctrine')
            ->getRepository(Website::class)
            ->findOneBy(['default' => true]);
    }
}
