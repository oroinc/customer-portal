<?php

namespace Oro\Bundle\FrontendBundle\GuestAccess;

/**
 * Represents a service that is used to decide whether an access is granted or not for a guest to a specific URL.
 */
interface GuestAccessDecisionMakerInterface
{
    public const URL_ALLOW = 1;
    public const URL_DISALLOW = 2;

    /**
     * Detects whether an access is granted or not for the given URL.
     */
    public function decide(string $url): int;
}
