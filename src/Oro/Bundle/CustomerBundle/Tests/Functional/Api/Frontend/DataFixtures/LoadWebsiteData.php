<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api\Frontend\DataFixtures;

use Oro\Bundle\WebsiteBundle\Tests\Functional\DataFixtures\LoadWebsite;

/**
 * Loads the default website belongs to the first organization from the database.
 */
class LoadWebsiteData extends LoadWebsite
{
    public const DEFAULT_WEBSITE = self::WEBSITE;
}
