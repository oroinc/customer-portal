<?php

namespace Oro\Bundle\WebsiteBundle\Provider;

use Oro\Bundle\WebsiteBundle\Entity\Website;

/**
 * Defines the contract for providing website data and information.
 *
 * Implementations of this interface are responsible for retrieving website entities,
 * their identifiers, and providing choice lists for website selection in forms and
 * other user interface components.
 */
interface WebsiteProviderInterface
{
    /**
     * @return Website[]
     */
    public function getWebsites();

    /**
     * @return int[]
     */
    public function getWebsiteIds();

    /**
     * @return array
     */
    public function getWebsiteChoices();
}
