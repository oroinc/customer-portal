<?php

namespace Oro\Bundle\FrontendBundle\GuestAccess\Provider;

interface GuestAccessUrlsProviderInterface
{
    /**
     * @return string[]
     */
    public function getAllowedUrls();

    /**
     * @return string[]
     */
    public function getRedirectUrls();
}
