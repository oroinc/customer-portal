<?php

namespace Oro\Bundle\FrontendBundle\GuestAccess\Provider;

/**
 * Interface for services that provide a list of patterns for URLs
 * for which access is granted for non-authenticated visitors.
 */
interface GuestAccessAllowedUrlsProviderInterface
{
    /**
     * @return string[]
     */
    public function getAllowedUrlsPatterns(): array;
}
