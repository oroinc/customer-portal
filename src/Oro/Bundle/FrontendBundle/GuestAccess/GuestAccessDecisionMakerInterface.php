<?php

namespace Oro\Bundle\FrontendBundle\GuestAccess;

interface GuestAccessDecisionMakerInterface
{
    const URL_ALLOW = 1;
    const URL_DISALLOW = 2;

    /**
     * Detects whether a given url is allowed or disallowed.
     *
     * @param string $url
     *
     * @return bool
     */
    public function decide($url);
}
