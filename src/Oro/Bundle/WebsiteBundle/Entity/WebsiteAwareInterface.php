<?php

namespace Oro\Bundle\WebsiteBundle\Entity;

/**
 * Interface for entities which are related to some website.
 */
interface WebsiteAwareInterface
{
    /**
     * @return Website|null
     */
    public function getWebsite();

    /**
     * @param Website $website
     * @return $this
     */
    public function setWebsite(Website $website);
}
