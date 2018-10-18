<?php

namespace Oro\Bundle\WebsiteBundle\Provider;

use Oro\Bundle\WebsiteBundle\Entity\Website;

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
