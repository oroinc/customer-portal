<?php

namespace Oro\Bundle\FrontendBundle\GuestAccess\Provider;

interface GuestAccessAllowedUrlsProviderInterface
{
    /**
     * @return string[]
     */
    public function getAllowedUrlsPatterns();
}
