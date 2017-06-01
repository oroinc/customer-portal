<?php

namespace Oro\Bundle\FrontendBundle\GuestAccess;

interface GuestAccessDecisionMakerInterface
{
    const URL_ALLOW = 1;
    const URL_DISALLOW = 2;
    const URL_REDIRECT = 4;

    /**
     * Detects whether a given url is allowed, disallowed or should be redirected.
     *
     * @param string $url
     *
     * @return bool
     */
    public function decide($url);
}
